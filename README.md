# Eloquent Model Futures

## Give your models a nice and predictable future
A package that lets you plan changes to your models in a simple manner.

## Installation
Require the package via composer
```bash
composer require dixie/eloquent-model-future
```

Run the package migrations to create a `futures` table, that will hold every future of your selected models.
```bash
php artisan migrate
```

Schedule the command to persist future plans
```php
$scheduler->command('future:schedule')->daily();
```

## Usage
On your desired models use the `HasFuture` trait.
```php
class User extends Model
{
    use Dixie\EloquentModelFuture\HasFuture;
}
```

Now you can plan out, edit and take away attribute changes, planned for the future.

Here is how you interact with your models future.

```php
$user = User::find(1);
$nextMonth = Carbon\Carbon::now()->addMonth();

// Plan a profile change for new years eve
$user->future()->plan([
    'bio' => 'Happy developer time. Wooh!',
    'mood' => 'excited',
])->for($nextMonth);

// Does our user have any scheduled plans for next month?
$user->future()->anyPlansFor($nextMonth); // true

// How does our user look in the future
$user->future()->see($newYearsEve);
User {
  'attributes': {
      'id': 1,
      'name': 'John Doe',
      'bio': 'Happy developer time. Wooh!',
      'mood': 'excited',
      [...]
  }
}

// You can commit to the changes by future plans after you've seen them
// ... this will fill the `committed` column with todays date
$user->future()->see($newYearsEve)->commit(); // true

// Access all future plans for the given day
$futurePlans = $user->future()->getPlansFor(Carbon $date)
FutureCollection {
    Future {
        'attributes' => [
            'bio' => 'Happy [...]',
            'mood' => 'excited',
        ]
    }
}

// There are some helper methods that come with the FutureCollection
$futurePlans->original();   // Original user state. Returns a user instance.

$futurePlans->result();     // How the user will look when collection is applied to user.
$futurePlans->resultDiff()  // Shows which attributes has changed 
                            // and what the values would be before and after
```

---

## API Reference
| **Class**#*methodName* | Arguments | Returns | Note |
|------------------------|-----------|---------|------|
| **Future**#*untilDate* | Carbon | Eloquent\Builder | Query only futures scheduled between `date('now')` and the given date. This is an Eloquent scope. |
| **Future**#*uncommitted* | - | Eloquent\Builder | Query only uncommitted futures. This is an Eloquent scope. |
| **Future**#*committed* | - | Eloquent\Builder | Query only committed futures. This is an Eloquent scope. |
| **Future**#*futureable* | - | MorphTo | This is a standard Eloquent polymorphic relationship. *(Inverse of HasFuture#futures)* |
| **FutureCollection**#*original* | - | Model | Gets the model back with no data changed. |
| **FutureCollection**#*result* | - | Model | Gets the model back with all the future data filled. **It is not saved** |
| **FutureCollection**#*resultDiff* | - | Support\Collection | Gets a list of all fields that would change, with both *before* and *after* |
| **FuturePlanner**#*plan* | array | FuturePlanner | Set the attributes which should be persisted later. |
| **FuturePlanner**#*for* | Carbon | Future | Set the date for when the attributes should be persisted. |
| **FuturePlanner**#*see* | Carbon | Model | See the final result of a model for a given date. |
| **FuturePlanner**#*getPlans* | - | FutureCollection | Get all future plans for a model. |
| **FuturePlanner**#*getPlansFor* | Carbon | FutureCollection | Get all future plans for a model for the given day. |
| **FuturePlanner**#*getPlansUntil* | Carbon | FutureCollection | Get all future plans for a model, between now and the given date. |
| **FuturePlanner**#*hasAnyPlans* | - | boolean | See if model has any future plans at all. |
| **FuturePlanner**#*hasAnyPlansFor* | Carbon | boolean | See if model has any future plans for the given date. |
| **FuturePlanner**#*hasAnyPlansUntil* | Carbon | boolean | See if model has any future plans between now and the given date. |
| **HasFuture**#*futures*() | - | MorphMany | This is a standard Eloquent polymorphic relationship |
| **HasFuture**#*uncommittedFutures* | - | MorphMany | Same as `futures` but filtered to only include uncommitted futures |
| **HasFuture**#*commit* | - | boolean | This is a wrapper around `$model->save()` but it also sets the `committed` flag to `Carbon::now()` |
| **Future**#*forDate* | Carbon | Eloquent\Builder | Query only futures scheduled for the given day. This is an Eloquent scope. |

