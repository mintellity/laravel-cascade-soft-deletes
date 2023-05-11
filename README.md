# Cascade Soft Deletes for relations in your Laravel models.

Automatically soft delete related models when the parent model is soft deleted and restore them when the parent model is restored.

> :warning: **Restoring is experimental.** Only models whose deleted_at timestamp is greater than the parent model's deleted_at timestamp will be restored. This means that if you restore a parent model, all related models that were deleted before the parent model will not be restored.

## Installation

Add this repository to your `composer.json` file:
```json
{
  "repositories": [
    {
      "type": "github",
      "url": "https://github.com/mintellity/laravel-tabbed-sessions.git"
    }
  ]
}

```
You can install the package via composer:

```bash
composer require mintellity/laravel-cascade-soft-deletes
```

## Usage

Add the `Mintellity\LaravelCascadeSoftDeletes\Traits\CascadeSoftDeletes` trait to your model. You can remove the `Illuminate\Database\Eloquent\SoftDeletes` trait if you want to. Add each relation you want to cascade soft delete, to the `cascadeDeletes` array in your Model.

```php
class User extends Model
{
    use CascadeSoftDeletes;
    
    protected array $cascadeDeletes = ['orders'];
    
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
```

To also cascade restore, also add the `Mintellity\LaravelCascadeSoftDeletes\Traits\CascadeRestores` trait to your model. By default the `cascadeRestores` array will be the same as the `cascadeDeletes` array. If you want to restore other relations, you can add them to the `cascadeRestores` array.

```php
class User extends Model
{
    use CascadeSoftDeletes,
        CascadeRestores;
    
    protected array $cascadeDeletes = ['orders'];
    
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Mintellity GmbH](https://github.com/mintellity)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
