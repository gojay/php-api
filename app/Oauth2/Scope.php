<?php
namespace App\OAuth2;

class Scope implements \OAuth2\Storage\ScopeInterface
{
    protected $db;

    public function __construct($connection)
    {
        if (!$connection instanceof \PDO) {
            if (!is_array($connection)) {
                throw new \InvalidArgumentException('First argument to OAuth2\Storage\Pdo must be an instance of PDO or a configuration array');
            }
            if (!isset($connection['dsn'])) {
                throw new \InvalidArgumentException('configuration array must contain "dsn"');
            }
            // merge optional parameters
            $connection = array_merge(array(
                'username' => null,
                'password' => null,
            ), $connection);
            $connection = new \PDO($connection['dsn'], $connection['username'], $connection['password']);
        }
        $this->db = $connection;

        // debugging
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

	public function getDefaultScope($client_id = null)
    {
        if (!is_null($client_id)) {
            $stmt = $this->db->prepare($sql = 'SELECT scope FROM oauth_clients WHERE client_id = :client_id');
            $stmt->execute(compact('client_id'));
            return implode(' ', $stmt->fetch(\PDO::FETCH_NUM));
        }
        return null;
    }

    public function scopeExists($scope, $client_id = null)
    {
        if (!is_null($client_id)) {
            $stmt = $this->db->prepare($sql = 'SELECT supported_scopes scope FROM oauth_clients WHERE client_id = :client_id');
            $stmt->execute(compact('client_id'));
            $clientSupportedScopes = explode(' ', $stmt->fetchColumn());
            $scope = explode(' ', $scope);

            return (count(array_diff($scope, $clientSupportedScopes)) == 0);
        }
        return false;
    }
}
?>