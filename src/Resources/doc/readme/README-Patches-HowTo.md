Using LibrinfoCore command tools for patches
============================================

A custom command has been created to manage custom patches on vendors (awaiting official release of patches)

##### Generating a patch file

Put your patched file into any folder you want in your Symfony project (should be in your src/ or in app/)

Execute the following command :

```$ app/console librinfo:patchs:generate path/to/original-file path/to/your/modified-file path/to/target-file```

```path/to/original-file``` can be a github URL (raw file URL) or any URL, or a path to a local file. This file is used to be compared when generating the patch file.
```path/to/your/modified-file``` can be a github URL (raw file URL) or any URL, or a path to a local file. This file contains your code modification to be applied.
```path/to/target-file``` must be a relative path to the file that will be patched (relative to the Symfony project root (the folder containing app/, src/, vendor/ ...))

This command creates the patch file (e.g. : ```1447941862.txt```) under ```BlastCoreBundle\Tools\Patches\``` directory.
It adds an entry into ```patches.yml``` under ```BlastCoreBundle\Tools\Patches\``` directory.

##### Listing patch files managed by the tool

Execute the following command :

```$ app/console librinfo:patchs:list``` to see all managed patches by the tool.

E.g. :

```
Listing available patches:


     - - - -
   id: 1447941862
   enabled: true
   patched: true
   targetFile: vendor/symfony/symfony/src/Symfony/Component/Form/ChoiceList/LazyChoiceList.php
   patchFile: src/Librinfo/CoreBundle/Command/../Tools/Patches/patches/1447941862.txt
     - - - -
```

All patched files will not be « re-patched » if it is flagged ```patched: true```.
If you want to force a patch to be re-patched, just edit ```patches.yml``` and set ```patched: false```.

##### Apply patches

Execute the following command :

```$ app/console librinfo:patchs:apply```

It will apply patches by parsing ```patches.yml```.

##### Apply patches when using composer.phar

A composer command has been created in order to automate the patching process when doing composer updates and installations.

To enable this feature, just add this line in your composer.json :

```
{
    "name": "you/your-project",
    # ...

    "scripts": {
        "post-install-cmd": [

            # ...

            "Blast\\CoreBundle\\Tools\\Patches\\Patcher::applyPatches"
        ],
        "post-update-cmd": [

            # ...

            "Blast\\CoreBundle\\Tools\\Patches\\Patcher::applyPatches"
        ]
    },

    # ...

}
```
