The Contribution Guide
======================

.. note::

   | This section is based on the great `Symfony documentation <http://symfony.com/doc/current>`_.
   | The following is a set of guidelines for contributing to `Blast <https://github.com/blast-project>`_ on GitHub.

How to install Blast to contribute?
------------------------------------

Before you start contributing you need to have your own local environment for editing things.

To install Blast main application from our main repository and contribute, run the following command:

.. code-block:: bash

    $ composer create-project -s dev blast-project/blast



Reporting bugs and suggesting enhancements
-------------------------------------------

Before creating issues, please check
`this list <https://github.com/issues?q=is%3Aissue+user%3Ablast-project+sort%3Acomments-desc>`_
as you might find out that you don't need to create one. When you are creating
a `new issue <https://github.com/blast-project/CoreBundle/issues/new>`_,
please include as many details as possible to help maintainers reproduce the problem
or understand your suggestion.


Submitting changes
------------------

Like most projects, we propose a standard `GitHub Flow <https://guides.github.com/introduction/flow/index.html>`_
for contributions:

1. Fork
2. Create a topic branch
3. Add commits
4. Create a Pull Request
5. Discuss and review your code
6. Merge

If you want to submit changes, please send a `GitHub Pull Request <https://github.com/blast-project/CoreBundle/pull/new/master>`_
with a clear list of what you've done (read more about `pull requests <https://help.github.com/categories/collaborating-with-issues-and-pull-requests/>`_).

Please make sure all of your commits are atomic (one feature per commit) and always
write a clear log message for your commits to help maintainers understand and review your submission.
