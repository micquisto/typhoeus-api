# Typhoeus API

A Typhoeus package built in Laravel.

***
### Production
## 1. Install package
* Edit *composer.json* from you base Typhoeus app and insert this repository:
```json
{
    "repositories": [
        {
            "type": "git-bitbucket",
            "url": "https://bitbucket.org/gogreenentinc/typhoeus-api.git"
        }
    ]
}
```
* Run `composer require typhoeus/api`
* Enter credentials if required
## 2. Check if the package is installed
* Run `composer show` and look for the package `typhoeus/api`

***
### Development
If you need to develop this app, do the following:
## 1. Install package (using workbench)
* Create a directory *workbench/typhoeus/api/* from the root of your Typhoeus app
* Edit *composer.json* from the root of the app and insert the repository:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "workbench/typhoeus/api"
        }
    ]
}
```
* Run `composer require typhoeus/api "@dev"`
* Enter credentials if required
* Composer will create a symlink of your app from workbench to the vendor directory
## 2. Check if the package is installed
* Run `composer show` and look for the package `typhoeus/api`
