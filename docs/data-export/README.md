# Data Export
> Documentation is a WIP.


Exporting Data (or "reports" as they are often known) is simple using Admin's Data Export system. Simply navigate to `Utilities › Export Data`, select the source you'd like and the format you'd like it in.


## Creating Sources

Sources are classes which return data from the database and return it in a consistent way which is understood by the format classes. Any component in a Nails application can provide a source.

To generate a source in your app create a class in the following directory: `src/DataExport/Source/` it should have the namespace `App\DataExport\Source` and implement the `Nails\Admin\Interfaces\DataExport\Source` interface.

An example is shown below, but for more details please see the source of the interface.

```php
<?php

namespace App\DataExport\Source;

use Nails\Admin\Interfaces\DataExport\Source;
use Nails\Factory;

class Books implements Source
{
    public function getLabel()
    {
        return 'Books';
    }

    // --------------------------------------------------------------------------

    public function getFileName()
    {
        return 'books';
    }

    // --------------------------------------------------------------------------

    public function getDescription()
    {
        return 'Exports all books in the database';
    }

    // --------------------------------------------------------------------------

    public function isEnabled()
    {
        return true;
    }

    // --------------------------------------------------------------------------

    public function execute($aData = [])
    {
        $oBookModel = Factory::model('Book', 'app');
        $aBooks     = $oBookModel->getAll(null, null, $aData);

        $oOut = (object) [
            'label'    => $this->getLabel(),
            'filename' => $this->getFileName(),
            'fields'   => ['id', 'title', 'author', 'published'],
            'data'     => [],
        ];

        foreach ($aBooks as $oBook) {
            $oOut->data[] = [
                $oBook->id,
                $oBook->title,
                $oBook->author,
                $oBook->published,
            ];
        }

        return $oOut;
    }
}

```

> *Note: The namespace to use if implementing a data source in a component (not the root app) will be `{ComponentNamespace}/DataExport/Source`* 


### Combining multiple files in a single Source

Sometimes it is nessecary to bundle multiple files together in a single source, the use case is usually a one-to-many relationship (e.g. `book` and `book_review`). This is easily acocmplished by returning an array in the `execute()` method, an array of objects which each describe the file to include. Each of these items will be fed individually to the chosen formatter and then all zipped together into a single archive.

```
public function execute($aData = [])
{
    $oBookModel = Factory::model('Book', 'app');
    $aBooks     = $oBookModel->getAll(null, null, $aData + ['expand' => ['review']]);

    $aOut = []
        'book' => (object) [
            'label'    => 'Books',
            'filename' => book,
            'fields'   => ['id', 'title', 'author', 'published'],
            'data'     => [],
        ],
        'review' => (object) [
            'label'    => 'Reviews',
            'filename' => 'book_review',
            'fields'   => ['id', 'book_id', 'review', 'author'],
            'data'     => [],
        ],
    ];

    foreach ($aBooks as $oBook) {

        $aOut['book']->data[] = [
            $oBook->id,
            $oBook->title,
            $oBook->author,
            $oBook->published,
        ];

        foreach ($oBook->review->data as $oReview) {
            $aOut['review']->data[] = [
                $oReview->id,
                $oBook->id,
                $oReview->review,
                $oReview->author,
            ];
        }
    }

    return $aOut;
}
```

The above will result in a zip file being created containing two files: `book` and `book_review`.


## Creating Formats

Formatters take the data returned by a source and compile it down to a string which is then saved to a file and sent to the user. If you need to create a new type of format which is not already available then create a new Format class at `src/DataExport/Format/`, give it the namespace `App\DataExport\Format` and implement the `Nails\Admin\Interfaces\DataExport\Format` interface.

An example is shown below, but for more details please see the source of the interface.

```php
<?php

namespace Nails\Admin\DataExport\Format;

use Nails\Admin\Interfaces\DataExport\Format;
use Nails\Factory;

class CustomFormat implements Format
{
    public function getLabel()
    {
        return 'Custom Format';
    }

    // --------------------------------------------------------------------------

    public function getDescription()
    {
        return 'This is a custom format';
    }

    // --------------------------------------------------------------------------

    public function execute($oData)
    {
        $oView = Factory::service('View');
        
        $sData = '* Generate file data *';

        return (object) [
            'filename'  => $oData->filename,
            'extension' => 'custom',
            'data'      => $sData,
        ];
    }
}

```

> *Note: The namespace to use if implementing a data source in a component (not the root app) will be `{ComponentNamespace}/DataExport/Format`* 


## Exporting Data Programatically

To export data programatically (e.g. in a cron job) you may use the `DataExport` service:

```
$oDataExport = Factory::service('DataExport', 'nailsapp/module-admin');
```

This model provides you with the following methods:

### `getAllSources()`
Returns an array of all available sources.

### `getSourceBySlug($sSlug)`
Returns a particular source by its slug.

### `getAllFormats()`
Returns an array of all available formats.

### `getFormatBySlug($sSlug)`
Returns a particular format by its slug.

### `export($sSourceSlug, $sFormatSlug, $aSourceData = [], $bOutputToBrowser = true)`
Exports a source using a format and optionally pass in data for the source and send it to the browser.

*Note: this method creates a temporary file (details of which are returned as an object); you can use this information to move/save the file elsewhere if needed - the service will automatically delete the file when it destructs.*
