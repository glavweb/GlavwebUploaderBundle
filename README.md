Installation
============

## Get the bundle using composer

Add GlavwebUploaderBundle by running this command from the terminal at the root of
your Symfony project:

```bash
php composer.phar require glavweb/uploader-bundle
```


## Enable the bundle

To start using the bundle, register the bundle in your application's kernel class:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Glavweb\UploaderBundle\GlavwebUploaderBundle(),
        // ...
    );
}
```

### Configure the bundle

This bundle was designed to just work out of the box. The only thing you have to configure in order to get this bundle up and running is a mapping.

```yaml
# app/config/config.yml

glavweb_uploader:
    mappings:
        gallery:
            providers :
                - glavweb_uploader.provider.image
            use_orphanage: true
            upload_directory:     %kernel.root_dir%/../web/uploads/gallery
            upload_directory_url: uploads/gallery
            max_size: 4194304 # 4Mb
            allowed_mimetypes: [image/jpeg, image/gif, image/png]
            
    orphanage:
        lifetime: 86400
        directory: %kernel.cache_dir%/uploader/orphanage
            
```

To enable the dynamic routes, add the following to your routing configuration file.

```yaml
#  app/config/routing.yml

glavweb_uploader:
    resource: "@GlavwebUploaderBundle/Resources/config/routing.xml"
    prefix:   /
```
