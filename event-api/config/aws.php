return [
'credentials' => [
'key'    => 'YOUR_AWS_ACCESS_KEY_ID',
'secret' => 'YOUR_AWS_SECRET_ACCESS_KEY',
],
'region' => 'us-west-2',
'version' => 'latest',

// You can override settings for specific services
'Ses' => [
'region' => 'us-east-1',
],
];