Getting started with Blast CoreBundle
=====================================


Introduction
------------

The goal of this bundle is to make the use of SonataAdmin “view-models”
possible without writting a line of PHP, without loosing a feature of
Sonata, and importing the idea of composite settings using lots of
characteristics of an admin (its direct inheritance tree, the traits
used by its Entity, the inheritance tree of its Entity…), making things
more flexible, extendable, reusable and maintenable through many bundles
and uses.

This bundle is the next step after the SonataAdminBundle. Configure an
entire backend bundle filling only YAML files… Try it!

It is also the core of `Libre Informatique`_\ ’s Symfony 2/3 projects.

Example
-------

I want to design and create a bundle as a toolbox for other bundles’
entities. It will provide traits for email addresses and phonenumbers,
for instance (cf. `BlastBaseEntitiesBundle`_).

Using the BlastCoreBundle, your “base” bundle will carry the traits, but
also the way to display properties given by its traits in a SonataAdmin
(which becomes a CoreAdmin) CRUD. Then using the traits of your “base”
bundle in the entities of other bundles (also implementing the
BlastCoreBundle) will add the fields naturally, the columns in the list
of objects, etc… as you set up for your trait in your “base” bundle,
without having to write a line for this.

Imagine this feature appliable to 50 entities distributed in 10 bundles,
and count in your mind the number of saved lines, the number of
potential bugs avoided and the ease of maintenance when you want to
change the nature of the field used by the provided email address or
phonenumber… This is what the BlastCoreBundle permits.
