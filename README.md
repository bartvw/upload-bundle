BVWUploadBundle
===============

The goal of this bundle is to provide tools to handle file uploads.


Currently it includes one form type, `image_upload`. This presents a preview of the current image and a button which opens a modal dialog (twitter bootstrap required) in which a new image can be uploaded and then cropped. 

## Configuration

Sample configuration (config.yml)

    bvw_upload:

      upload:
        base_path: ../web/upload/temp   # where to store images
        base_url: /upload/temp          # public url for image store

      formats:
        post:
          default:
            width:        600
            height:       400
            mode:         limit
          small:
            width:        100
            height:       100
            mode:         fit
          thumbnail:
            width:        200
            height:       200
            mode:         center


### About formats

A format specifies how an uploaded image is processed. A format must have at least
one sub-format called *default*. This specifies what happens to the uploaded file.
If you specifiy additional sub-formats, the service will automatically create copies
of the uploaded file using their specifications. They can be referred to using
the subformats name (in the example they are called *thumbnail* and *small*.

The *mode* setting specifies how to process the image. There are four modes:

**limit**: Only resizes the image if it exceeds the specified width or height. Keeps aspect ratio.

**scale**: Fit image within dimensions but keep aspect ratio. Scales the image up if it's smaller
than the target dimensions. Note that the resulting height or width can be smaller than the
target dimensions if the aspect ratio of the given image doesn't match the target aspect ratio.

**fit**: Same as scale, except that the difference in height or width between the resized image and the target
dimensions will be filled with a color that can be specified with an extra `background_color` setting (default white)

**center**: Resize the image so that it's either exactly as wide or exactly as high as the target format and then
cut off the parts exceeding the dimensions (equally at the top and bottom or left and right).

For more formats see inside the `ImageGenerator` class.


## How to use

        $formBuilder
            ->add('image', 'image_upload', array('format' => 'avatar')))
        ;

Required options:

* `format`: an identifier refering to a format-spec in your config,
    which tells the service how to process the uploaded image.

Optional:

* `crop_area_width` and `crop_area_height` specify the dimensions of the
    JCrop cropping area inside the modal that is shown after uploading.
* `use_aspect_ratio`: set to true if JCrop should use a fixed aspect ratio
    (the aspect ratio of your target format) for cropping
* `preview`: specify a sub-format to use for displaying a thumbnail
    in the form widget. If left out, *default* is used

### Resulting value

The value that results from this form type is a unique name for the stored image.
You can store it as a string. You can get the public URL from the storage service like this:

    $url = $this->container->get('bvw_upload.storage')->getPublicUrl($name);

**TODO:** Create a twig extension to make this easy to use within templates

## Storage

The bundle hands the generated images to a storage service, which abstracts the images into 'storage objects'.
These objects are referenced by name. The storage implementation automatically generates a unique name
for each stored object.

Two storage service implementations are included in the bundle, one for a
local filesystem (enabled by default, easiest touse) and an Amazon S3 implementation for storing
your images in the cloud.

The default configuration stores your images in your project's `web/upload` directory. To change this,
put the following in your configuration:

    bvw_upload:
        localstorage:
            base_path:  ../web/media    # relative to your app.php or absolute!
            base_url:   /media          # public url of the above path (can be a full url as well)

To use S3 storage instead, include this:

    bvw_upload:
        storage: s3
        s3storage:
            key:        # your s3 api key
            secret:     # your s3 private key
            bucket:     # your s3 bucket
            region:     # your s3 region



## TODO

* improve documentation
* include a Twig helper to get the public URL of a storage object based on the object name
* handle deletes
* clean up unused images from storage? (how?)