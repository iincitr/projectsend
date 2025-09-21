<?php
namespace ProjectSend\Classes;

class Folders
{
    protected $folders;
    protected $arranged_folders;
    protected $dbh;
    protected $logger;

    public function __construct()
    {
        global $dbh;
        $this->dbh = $dbh;
        $this->logger = new \ProjectSend\Classes\ActionsLog;
    }

    function makeFolderBreadcrumbs($from_folder_id, $url = BASE_URI) {
        $base_url = strtok($url, '?');
        $parsed = parse_url($url);
        if (!empty($parsed['query'])) {
            $query = $parsed['query'];
            parse_str($query, $params);
            $params_remove = ['folder_id', 'search', 'assigned', 'uploader'];
            foreach ($params_remove as $param) {
                unset($params[$param]);
            }
        } else {
            $params = [];
        }
    
        $elements = [
            [
                'url' => $base_url,
                'name' => 'Files root',
            ],
        ];
    
        if (!empty($from_folder_id)) {
            $folder = new \ProjectSend\Classes\Folder($from_folder_id);
            $nested = $folder->getHierarchy();
            if (!empty($nested)) {
                $nested = array_reverse($nested);
    
                foreach ($nested as $folder) {
                    $params['folder_id'] = $folder['id'];
                    $url = ($folder['id'] != $from_folder_id) ? $base_url.'?'.http_build_query($params) : null;
                    $elements[] = [
                        'url' => $url,
                        'name' => $folder['name'],
                    ];
                }
            }
        }
    
        return $elements;
    }

    function getFolders($arguments = [])
    {
        // // Existing public flag fix
        // $queryx = "UPDATE `tbl_folders` set public = 1 ";
        // $statement = $this->dbh->prepare($queryx);
        // $statement->execute();

        // Initialize $folders as an empty array
        $folders = [];
        
        // Get client access level if not provided
        if (!isset($arguments['role']) && isset($arguments['user_id'])) {
            $arguments['role'] = $this->getUserRole($arguments['user_id']);
            if ($arguments['role'] === 'Client' && !isset($arguments['client_id'])) {
                $arguments['client_id'] = $arguments['user_id'];
            }
        }
    
        $query = "SELECT DISTINCT f.* FROM " . TABLE_FOLDERS . " f";
        $params = [];
        if (isset($arguments['role']) && $arguments['role'] === 'Client' && isset($arguments['client_id'])) {
            $query .= " WHERE (
            -- Folders created by the client
            f.user_id = :client_created
            OR
            -- Get folders that contain files created by current user (added condition)
            EXISTS (
                SELECT 1
                FROM " . TABLE_FILES . " tf
                WHERE tf.folder_id = f.id
                AND tf.user_id = :current_user_id
            )
            OR
            -- Get folders through direct file assignments or group memberships
            EXISTS (
                SELECT 1
                FROM " . TABLE_FILES_RELATIONS . " fr
                JOIN " . TABLE_FILES . " tf ON fr.file_id = tf.id
                WHERE tf.folder_id = f.id
                AND fr.hidden = 0
                AND (
                    -- Direct client assignment
                    fr.client_id = :client_id
                    OR
                    -- Group assignment
                    fr.group_id IN (
                        SELECT group_id
                        FROM " . TABLE_MEMBERS . "
                        WHERE client_id = :client_id_groups
                    )
                )
            )
            OR
            -- Include all parent folders of accessible folders
            f.id IN (
                WITH RECURSIVE folder_hierarchy AS (
                    -- Base case: Get folders with directly accessible files
                    SELECT DISTINCT tf.folder_id as id, fld.parent
                    FROM " . TABLE_FILES_RELATIONS . " fr
                    JOIN " . TABLE_FILES . " tf ON fr.file_id = tf.id
                    JOIN " . TABLE_FOLDERS . " fld ON tf.folder_id = fld.id
                    WHERE fr.hidden = 0
                    AND (
                        fr.client_id = :client_id_hierarchy
                        OR
                        fr.group_id IN (
                            SELECT group_id
                            FROM " . TABLE_MEMBERS . "
                            WHERE client_id = :client_id_groups_hierarchy
                        )
                    )
                    UNION ALL
                    -- Recursive case: Get all parent folders
                    SELECT f2.id, f2.parent
                    FROM " . TABLE_FOLDERS . " f2
                    INNER JOIN folder_hierarchy fh ON f2.id = fh.parent
                )
                SELECT id FROM folder_hierarchy
            )
        )";
        $params[':client_created'] = $arguments['client_id'];
        $params[':current_user_id'] = $arguments['client_id'];
        $params[':client_id'] = $arguments['client_id'];
        $params[':client_id_groups'] = $arguments['client_id'];
        $params[':client_id_hierarchy'] = $arguments['client_id'];
        $params[':client_id_groups_hierarchy'] = $arguments['client_id'];
            
            // Parent folder filter for clients
            if (array_key_exists('parent', $arguments)) {
                if (is_null($arguments['parent'])) {
                    $query .= " AND f.parent IS NULL";
                } else {
                    $query .= " AND f.parent = :parent";
                    $params[':parent'] = (int)$arguments['parent'];
                }
            }
        } else {
            // Admin access remains unchanged...
            $where_conditions = [];
            if (array_key_exists('parent', $arguments)) {
                if (is_null($arguments['parent'])) {
                    $where_conditions[] = "f.parent IS NULL";
                } else {
                    $where_conditions[] = "f.parent = :parent";
                    $params[':parent'] = (int)$arguments['parent'];
                }
            }
    
            if (isset($arguments['search'])) {
                $where_conditions[] = "(f.name LIKE :name OR f.slug LIKE :slug)";
                $search_terms = '%' . $arguments['search'] . '%';
                $params[':name'] = $search_terms;
                $params[':slug'] = $search_terms;
            }
    
            if (isset($arguments['include_public']) && $arguments['include_public'] == true) {
                $where_conditions[] = "f.public = :public";
                $params[':public'] = '1';    
            }
    
            if (isset($arguments['user_id'])) {
                $where_conditions[] = "f.user_id = :user_id";
                $params[':user_id'] = $arguments['user_id'];
            }
            
            if (isset($arguments['public_or_client']) && $arguments['public_or_client'] == true) {
                $where_conditions[] = "(f.public = :public_client OR f.user_id = :client_id)";
                $params[':public_client'] = '1';
                $params[':client_id'] = $arguments['client_id'];
            }
    
            if (!empty($where_conditions)) {
                $query .= " WHERE " . implode(" AND ", $where_conditions);
            }
        }
    
        $query .= " ORDER BY f.name ASC";
    
        $statement = $this->dbh->prepare($query);
        $statement->execute($params);
        
        // Initialize $folders before using it
        $folders = [];
        
        if ($statement->rowCount() > 0) {
            $statement->setFetchMode(\PDO::FETCH_ASSOC);
            while ($row = $statement->fetch()) {
                $obj = new \ProjectSend\Classes\Folder($row['id']);
                $folders[$row['id']] = $obj->getData();
            }
        }
    
        $this->folders = $folders;
        return $this->folders;
    }


    function getUserRole($user_id)
    {
        $query = "SELECT r.name FROM " . TABLE_USERS . " u
                  JOIN " . TABLE_ROLES . " r ON u.role_id = r.id
                  WHERE u.id = :user_id";
        $statement = $this->dbh->prepare($query);
        $statement->execute([':user_id' => $user_id]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? $result['name'] : null;
    }

    function getAllArranged($parent = null, $depth = 0, $include = [])
    {
        $data = [];
        $folders = $this->getFolders(['parent' => $parent]);
        if (!empty($folders)) {
            foreach ($folders as $folder_id => $folder) {
                if (!empty($include) && !in_array($folder_id, $include)) {
                    continue;
                }

                // Set depth based on parent
                if ($folder['parent'] == null) {
                    $folder['depth'] = 0;
                    $currentDepth = 0;
                } else {
                    $folder['depth'] = $depth + 1;
                    $currentDepth = $depth + 1;
                }
                
                // Get child elements with current depth
                $folder['children'] = $this->getAllArranged($folder['id'], $currentDepth, $include);
                $data[] = $folder;
            }
        }
    
        return $data;
    }

    function renderSelectOptions(&$folders = [], $arguments = [])
    {
        $return = '';
        if (empty($folders)) {
            return $return;
        }

        foreach ($folders as $folder) {
            $depth_indicator = ($folder['depth'] > 0) ? str_repeat('&mdash;', $folder['depth']) . ' ' : false;
            $selected = (!empty($arguments['selected']) && $arguments['selected'] == $folder['id']) ? 'selected="selected"' : '';
            if (!empty($arguments['ignore']) && in_array($folder['id'], $arguments['ignore'])) {
                continue;
            }
            $return .= '<option '.$selected.' value="'.$folder['id'].'">'.$depth_indicator . $folder['name'].'</option>';
            
            if (!empty($folder['children'])) {
                $return .= $this->renderSelectOptions($folder['children'], $arguments);
            }
        }

        return $return;
    }
}
