Installation
============

### Get the bundle using composer

Add GlavwebUploaderBundle by running this command from the terminal at the root of
your Symfony project:

```bash
php composer.phar require glavweb/uploader-bundle
```


### Enable the bundle

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
        entity_images:
            providers :
                - glavweb_uploader.provider.image
            use_orphanage: true
            upload_directory:     %kernel.root_dir%/../web/uploads/entity_images
            upload_directory_url: uploads/entity_images
            max_size: 4194304 # 4Mb
            allowed_mimetypes: [image/jpeg, image/gif, image/png]
            
    orphanage:
        lifetime: 86400
        directory: %kernel.cache_dir%/uploader/orphanage
            
```

To add resources to a twig confinuration.

```
twig:
    ...
    form:
        resources:
            ...
            - 'GlavwebUploaderBundle:Form:fields.html.twig'

```

To enable the dynamic routes, add the following to your routing configuration file.

```yaml
#  app/config/routing.yml

glavweb_uploader:
    resource: "@GlavwebUploaderBundle/Resources/config/routing.xml"
    prefix:   /
```

### Execute "assets:install".

```
php app/console assets:install
```

Basic Usage
===========

1. Added annotations for the entity which needs to support "GlavwebUploadable".
"@Glavweb\Uploadable" before you can define an entity class:

```
use Glavweb\UploaderBundle\Mapping\Annotation as Glavweb;

/**
 * Entity
 * 
 * @Glavweb\Uploadable
 */
class Entity
{
}
```

And another annotation "@Glavweb\UploadableField" before defining the properties of a many-to-many:

```
/**
 * @var \Doctrine\Common\Collections\Collection
 * 
 * @ORM\ManyToMany(targetEntity="Glavweb\UploaderBundle\Entity\Media", inversedBy="entities", orphanRemoval=true)
 * @ORM\OrderBy({"position" = "ASC"})
 * @Glavweb\UploadableField(mapping="entity_images", nameAddFunction="addImage", nameGetFunction="getImages")
 */
private $images;
```

End add default collection in constructor:

```
/**
 * Constructor
 */
public function __construct()
{
    ...
    $this->images = new \Doctrine\Common\Collections\ArrayCollection();
}
```

2. Add the field "glavweb_uploader_dropzone" in the form:

```
$builder
    ->add('images', 'glavweb_uploader_dropzone', array(
        'mapped' => false
    ))
;
```

3. While saving form running method "handleUpload()":

```
$uploaderManager = $this->get('glavweb_uploader.uploader_manager');
if ($form->isValid()) {
    $uploaderManager->handleUpload($entity);

    ...
}
```

## Events

Bundle has 3 events:

- glavweb_uploader.validation; // First event, will execute before your file will be uploaded
- glavweb_uploader.pre_upload; // Will execute before your file will be uploaded
- glavweb_uploader.post_upload. // Will execute after your file will be uploaded

### Example

As example we use post upload event.

services.yml: 
    
    post_upload_listener:
        class: AppBundle\Listener\PostUploadListener
        tags:
            - { name: kernel.event_listener, event: glavweb_uploader.post_upload, method: onPostUpload }

Listener class: 

    namespace AppBundle\Listener;
    
    use Glavweb\UploaderBundle\Event\PostUploadEvent;
    
    class PostUploadListener
    {
        /**
         * @param PostUploadEvent $event
         */
        public function onPostUpload(PostUploadEvent $event)
        {
            // Some logic
        }
    }

Other listeners work on a similar logics.

Also you can define listeners only for your context, as example if context is "article":

    article_post_upload_listener:
        class: AppBundle\Listener\ArticlePostUploadListener
        tags:
            - { name: kernel.event_listener, event: glavweb_uploader.post_upload_article, method: onPostUpload }

