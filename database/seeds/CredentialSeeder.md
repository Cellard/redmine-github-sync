Таблица содержит персональные ключи, поэтому пишите свой сидер, если хотите...

```php
public function run()
{
    $user = \App\User::query()->where('email', 'pm@101media.ru')->first();

    DB::table('credentials')->updateOrInsert(
        ['user_id' => $user->getKey(), 'server_id' => 'helpdesk.101m.ru'],
        ['api_key' => '...']
    );

    DB::table('credentials')->updateOrInsert(
        ['user_id' => $user->getKey(), 'server_id' => 'git.101m.ru'],
        ['api_key' => '...']
    );
}
```
