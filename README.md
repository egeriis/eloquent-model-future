# Laravel Model Futures

## Give your models a nice and predictable future
A package that lets you plan changes to your models in a simple manner.

## Installation
Require the package via composer
```bash
composer require dixie/laravel-model-future
```

Run the package migrations to create a `futures` table, that will hold every future of your selected models.
```bash
php artisan migrate --path="vendor/dixie/laravel-model-future/src/migrations"
```

## Usage
On your desired models use the `HasFuture` trait.
```php
class User extends Model
{
    use Dixie\ModelFutures\HasFuture;
}
```

Now you can plan out, edit and take away attribute changes, planned for the future.

Here is how you interact with your models future.

```
$user = User::find(1);
$newYearsEve = Carbon::now();

// Plan a profile change for new years eve
$user->future()->plan([
    'bio' => 'Happy new years everybody!',
    'mood' => 'excited',
])->for($newYearsEve);

// Does our user have any scheduled plans for 2017?
$user->future()->anyPlansFor($newYearsEve|null);
{
    'bio': 'Happy new years everybody!',
    'mood': 'excited'
}

// How does our user look in the future
$user->future()->see($newYearsEve);
User {
  'attributes': {
      'id': 1,
      'name': 'John Doe',
      'bio': 'Happy new years everybody!',
      'mood': 'excited',
      [...]
  }
}
```