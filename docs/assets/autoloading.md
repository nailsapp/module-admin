# Autoloading Assets

Components can request the main admin controller to globally load any kind of Helper, JS or CSS asset. This is useful should a module wish to make a component available accross the whole of admin, even outside of it's own supplied panels.

To do this you make use of the component's `data` property in `composer.json` (or `config.json` if the component is provided by the app). Simply place your desired assets in a property named `autoload`, namespaced under `nailsapp/module-admin`.

The following is an example showing all options:

```json
"extra":
{
    "nails" :
    {
        "moduleName": "myModule",
        "type": "module",
        "data": {
            "nailsapp/module-admin": {
                "autoload": {
                    "helpers": [
                        "myHelper"
                    ],
                    "assets": {
                        "js": [
                            "my-js.min.js"
                            [
                                "package.min.js",
                                "BOWER"
                            ]
                        ],
                        "jsInline": [
                            "alert('hi!');"
                        ],
                        "css": [
                            [
                                "package.css",
                                "BOWER"
                            ]
                        ],
                        "cssInline": [
                            "#element { background: pink; }"
                        ]
                    }
                }
            }
        }
    }
}
```

Note: Helpers will be loaded with the module's slug as the second parameter, i.e. it should be provided by the module itself.

Note: Notice that it is possible to provide the assets as either a string, or an array. If passed as a string it is assumed that the asset is provided by the app. An array allows you to manipulate the 1st and 2nd parameters of the Asset library's `load()` method.
