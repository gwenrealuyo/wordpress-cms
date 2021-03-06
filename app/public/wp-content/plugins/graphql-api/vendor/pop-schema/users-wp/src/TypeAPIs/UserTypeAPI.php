<?php

declare(strict_types=1);

namespace PoPSchema\UsersWP\TypeAPIs;

use PoP\ComponentModel\TypeDataResolvers\InjectedFilterDataloadingModuleTypeDataResolverTrait;
use PoP\Hooks\HooksAPIInterface;
use PoPSchema\QueriedObject\Helpers\QueriedObjectHelperServiceInterface;
use PoPSchema\SchemaCommons\DataLoading\ReturnTypes;
use PoPSchema\Users\ComponentConfiguration;
use PoPSchema\Users\TypeAPIs\UserTypeAPIInterface;
use WP_User;
use WP_User_Query;

/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
class UserTypeAPI implements UserTypeAPIInterface
{
    use InjectedFilterDataloadingModuleTypeDataResolverTrait;
    /**
     * @var \PoP\Hooks\HooksAPIInterface
     */
    protected $hooksAPI;
    /**
     * @var \PoPSchema\QueriedObject\Helpers\QueriedObjectHelperServiceInterface
     */
    protected $queriedObjectHelperService;
    public function __construct(HooksAPIInterface $hooksAPI, QueriedObjectHelperServiceInterface $queriedObjectHelperService)
    {
        $this->hooksAPI = $hooksAPI;
        $this->queriedObjectHelperService = $queriedObjectHelperService;
    }

    /**
     * Indicates if the passed object is of type User
     * @param object $object
     */
    public function isInstanceOfUserType($object): bool
    {
        return $object instanceof WP_User;
    }

    /**
     * @param string|int $userID
     * @return object|null
     */
    protected function getUserBy(string $property, $userID)
    {
        $user = get_user_by($property, $userID);
        if ($user === false) {
            return null;
        }
        return $user;
    }

    /**
     * @param string|int $userID
     * @return object|null
     */
    public function getUserById($userID)
    {
        return $this->getUserBy('id', $userID);
    }

    /**
     * @return object|null
     */
    public function getUserByEmail(string $email)
    {
        return $this->getUserBy('email', $email);
    }

    /**
     * @return object|null
     */
    public function getUserByLogin(string $login)
    {
        return $this->getUserBy('login', $login);
    }

    public function getUserCount(array $query = [], array $options = []): int
    {
        // Convert the parameters
        $options['return-type'] = ReturnTypes::IDS;
        $query = $this->convertUsersQuery($query, $options);

        // All results, no offset
        $query['number'] = -1;
        unset($query['offset']);

        // Limit users which have an email appearing on the input
        // WordPress does not allow to search by many email addresses, only 1!
        // Then we implement a hack to allow for it:
        // 1. Set field "search", as expected
        // 2. Add a hook which will modify the SQL query
        // 3. Execute query
        // 4. Remove hook
        $filterByEmails = $this->filterByEmails($query);
        if ($filterByEmails) {
            add_action('pre_user_query', [$this, 'enableMultipleEmails']);
        }

        // Execute the query. Original solution from:
        // @see https://developer.wordpress.org/reference/functions/get_users/#source
        // Only difference: use `total_count` => true, `get_total` instead of `get_results`
        $args                = \wp_parse_args($query);
        $args['count_total'] = true;
        $user_search = new \WP_User_Query($args);
        $ret = (int) $user_search->get_total();

        // Remove the hook
        if ($filterByEmails) {
            remove_action('pre_user_query', [$this, 'enableMultipleEmails']);
        }
        return $ret;
    }
    public function getUsers($query = array(), array $options = []): array
    {
        // Convert the parameters
        $query = $this->convertUsersQuery($query, $options);

        // Limit users which have an email appearing on the input
        // WordPress does not allow to search by many email addresses, only 1!
        // Then we implement a hack to allow for it:
        // 1. Set field "search", as expected
        // 2. Add a hook which will modify the SQL query
        // 3. Execute query
        // 4. Remove hook
        $filterByEmails = $this->filterByEmails($query);
        if ($filterByEmails) {
            add_action('pre_user_query', [$this, 'enableMultipleEmails']);
        }

        // Execute the query
        $ret = get_users($query);

        // Remove the hook
        if ($filterByEmails) {
            remove_action('pre_user_query', [$this, 'enableMultipleEmails']);
        }
        return $ret;
    }
    /**
     * Limit users which have an email appearing on the input
     * WordPress does not allow to search by many email addresses, only 1!
     * Then we implement a hack to allow for it:
     * 1. Set field "search", as expected
     * 2. Add a hook which will modify the SQL query
     * 3. Execute query
     * 4. Remove hook
     *
     * @param mixed[] $query
     */
    protected function filterByEmails(array &$query): bool
    {
        if (isset($query['emails'])) {
            $emails = $query['emails'];
            // This works for either 1 or many emails
            $query['search'] = implode(',', $emails);
            // But if there's more than 1 email, we must modify the SQL query with a hook
            if (count($emails) > 1) {
                return true;
            }
        }
        return false;
    }

    protected function convertUsersQuery(array $query, array $options = []): array
    {
        if ($return_type = $options['return-type'] ?? null) {
            if ($return_type == ReturnTypes::IDS) {
                $query['fields'] = 'ID';
            }
        }

        // Accept field atts to filter the API fields
        $this->maybeFilterDataloadQueryArgs($query, $options);

        // Convert parameters
        if (isset($query['name'])) {
            $query['meta_query'][] = [
                'key' => 'nickname',
                'value' => $query['name'],
                'compare' => 'LIKE',
            ];
            unset($query['name']);
        }
        if (isset($query['include'])) {
            // Transform from array to string
            $query['include'] = implode(',', $query['include']);
        }
        if (isset($query['exclude'])) {
            // Transform from array to string
            $query['exclude'] = implode(',', $query['exclude']);
        }
        if (isset($query['order'])) {
            // Same param name, so do nothing
        }
        if (isset($query['orderby'])) {
            // Same param name, so do nothing
            // This param can either be a string or an array. Eg:
            // $query['orderby'] => array('date' => 'DESC', 'title' => 'ASC');
        }
        if (isset($query['offset'])) {
            // Same param name, so do nothing
        }
        if (isset($query['limit'])) {
            // Maybe restrict the limit, if higher than the max limit
            // Allow to not limit by max when querying from within the application
            $limit = (int) $query['limit'];
            if (!isset($options['skip-max-limit']) || !$options['skip-max-limit']) {
                $limit = $this->queriedObjectHelperService->getLimitOrMaxLimit(
                    $limit,
                    ComponentConfiguration::getUserListMaxLimit()
                );
            }

            // Assign the limit as the required attribute
            $query['number'] = $limit;
            unset($query['limit']);
        }

        return $this->hooksAPI->applyFilters(
            'CMSAPI:users:query',
            $query,
            $options
        );
    }
    /**
     * Modify the SQL query, replacing searching for a single email
     * (with SQL operation "=") to multiple ones (with SQL operation "IN")
     */
    public function enableMultipleEmails(WP_User_Query $query): void
    {
        $qv =& $query->query_vars;
        if (isset($qv['search'])) {
            $search = trim($qv['search']);
            // Validate it has no wildcards, it's email (because there's a "@")
            // and there's more than one (because there's ",")
            $leading_wild = (ltrim($search, '*') != $search);
            $trailing_wild = (rtrim($search, '*') != $search);
            if (!$leading_wild && !$trailing_wild && false !== strpos($search, '@') && false !== strpos($search, ',')) {
                // Replace the query
                $emails = explode(',', $search);
                $searches = [sprintf("user_email IN (%s)", "'" . implode("','", $emails) . "'")];
                $replace = $this->get_search_sql($search, ['user_email'], false);
                $replacement = ' AND (' . implode(' OR ', $searches) . ')';
                $query->query_where = str_replace($replace, $replacement, $query->query_where);
            }
        }
    }

    /**
     * Function copied from WordPress source, because it is protected!
     *
     * @source wp-includes/class-wp-user-query.php
     *
     * Used internally to generate an SQL string for searching across multiple columns
     *
     * @since 3.1.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param string $string
     * @param array  $cols
     * @param bool   $wild   Whether to allow wildcard searches. Default is false for Network Admin, true for single site.
     *                       Single site allows leading and trailing wildcards, Network Admin only trailing.
     * @return string
     * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
     */
    protected function get_search_sql($string, $cols, $wild = false)
    {
        global $wpdb;

        $searches      = array();
        // Avoid PHPStan errors:
        //   Result of || is always false
        // $leading_wild  = ( 'leading' === $wild || 'both' === $wild ) ? '%' : '';
        // $trailing_wild = ( 'trailing' === $wild || 'both' === $wild ) ? '%' : '';
        $leading_wild  = '';
        $trailing_wild = '';
        $like          = $leading_wild . $wpdb->esc_like($string) . $trailing_wild;

        foreach ($cols as $col) {
            if ('ID' === $col) {
                $searches[] = $wpdb->prepare("$col = %s", $string);
            } else {
                $searches[] = $wpdb->prepare("$col LIKE %s", $like);
            }
        }

        return ' AND (' . implode(' OR ', $searches) . ')';
    }

    /**
     * @param string|int|object $userObjectOrID
     */
    protected function getUserProperty(string $property, $userObjectOrID): ?string
    {
        if (is_object($userObjectOrID)) {
            /** @var WP_User */
            $user = $userObjectOrID;
            return $user->$property;
        }
        return get_the_author_meta($property, $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserDisplayName($userObjectOrID): ?string
    {
        return $this->getUserProperty('display_name', $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserEmail($userObjectOrID): ?string
    {
        return $this->getUserProperty('user_email', $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserFirstname($userObjectOrID): ?string
    {
        return $this->getUserProperty('user_firstname', $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserLastname($userObjectOrID): ?string
    {
        return $this->getUserProperty('user_lastname', $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserLogin($userObjectOrID): ?string
    {
        return $this->getUserProperty('user_login', $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserDescription($userObjectOrID): ?string
    {
        return $this->getUserProperty('description', $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserWebsiteUrl($userObjectOrID): ?string
    {
        return $this->getUserProperty('user_url', $userObjectOrID);
    }
    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserSlug($userObjectOrID): ?string
    {
        return $this->getUserProperty('user_nicename', $userObjectOrID);
    }
    /**
     * @return string|int
     * @param object $user
     */
    public function getUserId($user)
    {
        return $user->ID;
    }

    /**
     * @param string|int|object $userObjectOrID
     */
    public function getUserURL($userObjectOrID): ?string
    {
        if (is_object($userObjectOrID)) {
            /** @var WP_User */
            $user = $userObjectOrID;
            $userID = $user->ID;
        } else {
            $userID = $userObjectOrID;
        }
        return get_author_posts_url($userID);
    }
}
