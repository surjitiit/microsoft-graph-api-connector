# microsoft-graph-api-project
This PHP SDK package using Microsoft Graph APIs (V1) to communicate with Entra server. This have basic setup with using user api. We can enhance it to explore other graph apis.

Example: include this code in your php script

```C
$tenant_details = array(
    "tenant_id" => 'scbkasc-asc-sac-as-c-ac3dsc-ss', // copy this from Entra portal
    "app_id" => 'scsd-sdc-scs-csd-dsc-s3dd-sd', // copy this from Entra portal
    "client_secret" => 'sdcjnsdjcdsd7cshwe73jdsdh', // copy this from Entra portal
);

$mg_users = new Mg_Users($tenant_details);
$userList = $mg_users->getUsers();
print_r($userList);
```

Note: On entra side we need to enable User.Read.All api permission to fetch users list
