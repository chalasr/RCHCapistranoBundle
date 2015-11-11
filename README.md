[![Build Status](https://travis-ci.org/chalasdev/CsvParserBundle.svg?branch=master)](https://travis-ci.org/chalasdev/CsvParserBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bed2f9ff-2ff5-484d-ac05-940d5aa226aa/mini.png)](https://insight.sensiolabs.com/projects/bed2f9ff-2ff5-484d-ac05-940d5aa226aa)

# chalasdev/capistrano-bundle

Simple CSV Import/Export Bundle with commande line/form template, and display data in markup.

##Requirements (before install)

- Symfony >= 2.7

##Install:

Download the bundle using [composer](http://getcomposer.org/) :

```composer require chalasdev/csvparserbundle dev-master```


##Configure

You have to add the service into your ```app/config/config.yml``` :

```yaml
# app/config/config.yml

services:
    import.csvtoarray:
        class: ChalasDev\Bundle\ChalasDevCsvParserBundle\Services\ConvertCsvToArray

#...

```

Now, update the parameters.yml file for your database informations .

Then, Add the following lines in your `AppKernel.php` file :

```php
//app/AppKernel.php
{
    //...

        new ChalasDev\Bundle\ChalasDevCsvParserBundle\ChalasDevCsvParserBundle(),

    //...
}
```

Add this to your app/config/routing.yml file :

```yaml
# app/config/routing.yml

//...

presta_technical_test_homepage:
    path:     /
    defaults: { _controller: ChalasDevCsvParserBundle:Default:index }

presta_technical_test_export:
    path:     /export
    defaults: { _controller: Namespace:Default:generateCsv }

//...

```

Install assets with this command :

```php app/console assets:install```

Create your database with the following :

```php app/console doctrine:database:create```

Create an entity corresponding to the CSV files to be parsed, take exemple on the ```/vendor/chalasdev/csvparserbundle/ChalasDev\Bundle\ChalasDevCsvParserBundle\Entity\Developer.php```


It's corresponding to the ```/web/data/import/developer_simple.csv``` file for store the datas.

Then, Build your database schema :

```php app/console doctrine:schema:update --force```

## Use

### Import

Place your csv file at your assets directory, into a ```/data/import/``` folder, like : ```/data/import/example.csv```

and run in console :

```php app/console import:csv```

That's all, your file content has been parsed and saved, line by line.

Run server and see them at ```http://localhost:8000/```

### Export

Create an ```export/``` folder in the ```data```directory, look like : ```/web/data/export/```

Extend the bundle DefaultController for override it.

```php
// src/Namespace/CsvBundle/NamespaceCsvBundle.php

<?php

namespace Namespace\CsvBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NamespaceCsvBundle extends Bundle
{
    public function getParent()
    {
        return 'ChalasDevCsvParserBundle';
    }
}
```

Create a controller for override the export action.

```php
// src/Namespace/CsvBundle/Controller/CsvController

<?php

namespace Namespace\CsvBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ChalasDev\Bundle\ChalasDevCsvParserBundle\Controller\DefaultController as BaseController;

class CsvController extends BaseController
{
    public function generateCsvAction(){

      $repo = $this->getDoctrine()
          ->getManager()
          ->getRepository('NamespaceCsvBundle:EntityToExport');
        $results = $repo->findAll();
        // var_dump($results);
        $count = count($results);

      //Requête
        $handle = fopen($this->get('kernel')->getRootDir().'/../web/data/export/export.csv', 'w');

        // DON'T FORGOT TO SET IT CORRESPONDING TO YOUR CSV ENTITY
        // Example of CSV columns names
        fputcsv($handle, array(
          'LASTNAME',
          'FIRSTNAME',
          'EMAIL',
          'LOCATION',
        ));

        //Champs
        for ($i=0; $i < $count; $i++) {
          # code...
          fputcsv($handle,array(
            $results[$i]->getLastname(),
            $results[$i]->getFirstname(),
            $results[$i]->getBadgeLabel(),
            $results[$i]->getBadgeLevel(),
          ));
         }

        rewind($handle);
            $content = stream_get_contents($handle);
        fclose($handle);

        return $this->redirect($this->generateUrl('presta_technical_test_homepage'));
    }
}

    //...

```

Click on The ```EXPORT``` link on ```http://localhost:8000/``` and find your new csv file into ```web/datas/filename.csv```

 Enjoy !

 Credits
 -------

 Author : [Robin Chalas](http://www.chalasdev.fr/)

 License
 -------

 [![License](http://img.shields.io/:license-gpl3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0.html)
