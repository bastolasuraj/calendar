<?php
// LDAP connection settings
$ldapHost     = "ldap://192.168.2.5";  // e.g. "ldap://ad01.company.local"
$ldapPort     = 389;                        // usually 389 (LDAP) or 636 (LDAPS)
$bindDn       = "cn=Fowler,cn=Local";
$bindPassword = "Rosewarne#Aggregate!";
$baseDn       = "cn=Fowler,cn=Local";        // adjust to your directory’s base DN

// Filter: any user (objectClass=person) with a telephoneNumber OR mobile attribute
$filter     = '(&(objectClass=person)(|(telephoneNumber=*)(mobile=*)))';
$attributes = ["cn", "givenName", "sn", "mail", "telephoneNumber", "mobile"];

// 1) Connect and configure
$ldap = ldap_connect($ldapHost, $ldapPort);
if (!$ldap) {
    die("Could not connect to LDAP server at {$ldapHost}:{$ldapPort}");
}
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

// 2) Bind (authenticate)
if (!@ldap_bind($ldap, $bindDn, $bindPassword)) {
    die("LDAP bind failed. Check bind DN and password.");
}

// 3) Perform the search
$search = ldap_search($ldap, $baseDn, $filter, $attributes);
if (!$search) {
    die("LDAP search failed.");
}

// 4) Fetch entries
$entries = ldap_get_entries($ldap, $search);

// 5) Normalize results
$users = [];
for ($i = 0; $i < $entries["count"]; $i++) {
    $e = $entries[$i];
    $users[] = [
        "dn"              => $e["dn"] ?? "",
        "cn"              => $e["cn"][0] ?? "",
        "givenName"       => $e["givenname"][0] ?? "",
        "sn"              => $e["sn"][0] ?? "",
        "mail"            => $e["mail"][0] ?? "",
        "telephoneNumber" => $e["telephonenumber"][0] ?? "",
        "mobile"          => $e["mobile"][0] ?? "",
    ];
}

// 6) Output as JSON (or handle however you like)
header('Content-Type: application/json; charset=utf-8');
echo json_encode($users, JSON_PRETTY_PRINT);

// 7) Clean up
ldap_unbind($ldap);
