# Autoloading Assets

Components can request the main admin controller to globally load any kind of JS or CSS asset. This is useful should a module wish to make a component available accross the whole of admin, even outside of it's own supplied panels.

To do this you make use of the component's `data` property in `composer.json` (or `config.json` if the component is provided by the app). Simply place your desired assets in a property named `adminAutoLoad`.

The following is a stripped down example:

```json
"extra":
{
    "nails" :
    {
        "moduleName": "myModule",
        "type": "module",
        "data": {
            "adminAutoLoad": {
                "js": [
                    "my-js.min.js"
                    [
                        "package.min.js",
                        "BOWER"
                    ]
                ],
                "css": [
                    [
                        "package.css",
                        "BOWER"
                    ]
                ]
            }
        }
    }
}
```

Notice that it is possible to provide the assets as either a string, or an array. If passed as a string it is assumed that the asset is provided by the app. An array allows you to manipulate the 1st and 2nd parameters of the Asset library's `load()` method.
