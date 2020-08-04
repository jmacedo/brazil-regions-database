# Database with all States, Cities and Districts of Brazil

This project uses an unoficial [Laravel 7.x](https://laravel.com/docs/7.x) micro-framework for console applications, [Laravel Zero](https://laravel-zero.com/), to load some SQL scripts with an unnormalized full list of states, cities and districts of Brazil and normalize them to use in your project.

Run ```php regions``` to see the available commands, including the command we prepare to use here, the ```db:populate``` command.

**To get things done:**

1. Configure your ```.env``` with the database configuration;
2. Just run ```php regions db:populate```;
3. The script will import 3 unnormalized tables: Estado, Municipio and Bairro, and normalize them to respectively states, cities and districts tables;
4. Now you can export the normalized data and import in your projects.

PS: Note that I used UUID for the primary keys.

## How to use UUID for primary key

- Create a trait to handle registers creation in models:

```
namespace App;

use Illuminate\Support\Str;

trait UuidForPrimaryKeyTrait
{
    public static function boot()
    {
        parent::boot();

        self::creating(function($model) {
            $model->id = Str::uuid();
        });
    }
}
```

- Use the trait in your models and configure them like this:

```
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UuidForPrimaryKeyTrait;

class State extends Model
{
    use UuidForPrimaryKeyTrait;
    protected $keyType = 'string';
    public $incrementing = false;

    // ... 
}
```

## Original source data of states, cities and districts

https://github.com/chandez/Estados-Cidades-IBGE

The submodule configured for this repository are located in ```database/sql/Estados-Cidades-IBGE```.

Note that not all districts are listed. You should use the districts in this database in an autocomplete field to improve your user experience and save the new districts to complete the list over the time.

#### Alternative source data, without districts (have to adapt the source code of this project)

https://github.com/chinnonsantos/sql-paises-estados-cidades

## Installation proccess from scratch (to make this project)

- Execute: ```composer create-project --prefer-dist laravel-zero/laravel-zero brazil-regions-database```
- If the application renaming script in composer fails, execute manually: ```php application app:rename``` and define the app name to "regions";
- Install the database add-on: ```php regions app:install database```;
- Install the .env add-on: ```php regions app:install dotenv```;
- Configure the environments variables to connect to database (including the default ```.env.example```);
- Create migrations for states, cities and districts: ```php regions make:migration <migration_name>```;
- Create Models for everyone (Estado, Municipio, Bairro, states, cities and districts): ```php regions make:model <ModelName>```;
- Create the command to normalize and migrate data: ```php regions make:command PopulateRegionsTablesCommand```;
