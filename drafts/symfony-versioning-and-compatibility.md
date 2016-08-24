[//]: # (TITLE: Symfony, versioning and compatibility)
[//]: # (DATE: 2016-08-26T08:00:00+01:00)
[//]: # (TAGS: symfony, composer, version, semver)

Versioning is more than just slapping an incremental number on your new release. A good version increase will let the users of your package know that there is more than just a new release. Most of us will understand that there is a difference between major and minor versions. However, using a proper versioning scheme can help people a lot with what the impact of the upgrade will be.

This post will try to give some context to the Symfony versions. What to be mindful of with a new release, be it a patch, minor or major version bump.

## Semantic versioning
First of all, Symfony uses [semantic versioning][semver] for determining new release numbers. This means that each version of Symfony is build up of three numbers, separated by a dot, i.e. `2.8.1` or `3.1.3`. Starting from right to left, we first have the least significant number. This is the patch version. An increment in patch versions (e.g., `3.1.2` to `3.1.3`) will mean that a bug was fixed. These changes should always be backwards compatible and will never introduce new features. Downgrading to a lower patch version should never break your application using the package. This is because you could not have been using something that is no longer there.

The next number is the minor version. An increment in minor versions (e.g., `3.0.12` to `3.1.0`) will mean that the public API was extended. These changes should always be backwards compatible, but will introduce new features. Downgrading to a lower minor version might break any code depended on a feature from that version. Thus it is recommended to put your lower version bound on a minor. Doing so will make your version constraints just loose enough to allow for better compatibility between packages.

Finally, we have the major version. An increment in major versions (e.g., `2.8.9` to `3.0.0`) will mean a change in the public API. These are not backwards compatible and often removal of old code or re-writes of code.

Using semantic versioning means that you can tell what the impact of the upgrade will be from the version number. Most likely, you will want to have an upper bound on the major version and a lower on the minor. For example, using the composer syntax that would be `>=3.1.0 <4.0.0` (or [shorthand][composer-caret] `^3.1`). This tells composer you want the API of the 3 major version with a feature from `3.1.0`.

## Long-Term-Support (LTS) releases
Besides normal versions, most software also have Long-Term-Support (LTS) releases. These are usually a specific version marked as LTS. Think of [Ubuntu][ubuntu-lts] or [Node.js][nodejs-lts]. These are special releases which form the basis for a long support period. This means it will receive patches for bugs and security fixes for a long time, but no more added features. In an enterprise setting these versions are preferable since they are the most stable. This is because they give a certain guarantee they will work for the foreseeable future.

LTS releases are usually created and maintained as fork of the project along side of it. If there is a fix in the LTS, this is ported to the newer versions if that bug is also present there. The Symfony projects uses this strategy for their LTS releases.

## Symfony releases and LTS versions
To quote the [Symfony docs][symfony-lts]:

> In the Symfony `2.x` branch, the LTS versions are `2.3`, `2.7` and `2.8`. Starting from the `3.x` branch, only the last minor version of each branch is considered LTS (e.g. `3.4`, `4.4`, `5.4`, etc.). 

At the time of writing, the current release is `3.1.3`. This means there are three LTS releases which will receive bug and security patches up to 36 months. For the `2.8` versions, this will be until November 2018.

From `2.8` and onward, when releasing an LTS, so is the next major version released (`3.0` in this case). Symfony does this because backwards incompatible changes need a migration period. In this period the code will contain the change and a layer to maintain compatibility. When releasing the new major version, the backwards compatibility is removed leaving the refactor. This means that the LTS and the following major release are feature wise identical. The only difference is the removal of the compatibility layer.

## Backwards compatibility promise
Finally, Symfony also has a [backwards compatibility promise][symfony-bcp]. These describe a set of rules which Symfony promises that your code will not break when upgrading. Before the promise it was sometime unclear which parts of the code may break between releases. The promise fixes this with a detailed set of rules. 

So on top of what you can expect of the semantic versioning, Symfony ties to make migrations as smooth as possible.

For example: A new way the `ChoiceType` worked upgrading from `2.x` to `3.x` was introduced. Starting from `3.0.0` [the keys and values of the choices are swapped][symfony-choice-option]. When upgrading, you would have to change all the code using choices types. Doing all this in one commit might mean a lot of changes. These large changes can breaking your application if you forgot something. To make this transition smoother, version `2.7.0` introduced the option `choices_as_values`. This option, when enabled, makes the choices behave as the `3.0.0` code. From verion `3.0.0` and higher this extra option no longer does anything. With version `3.1.0` it will even be deprecated. This allows you to change all your code before upgrading and doing so in smaller changes. Smaller changes mean usually mean less bugs.

To aid in transition, version `2.2` introduced [logging of deprecations in the Symfony Web Developer Toolbar (WDT)][symfony-dept-logging]. Moreover, [there is a special PHPUnit bridge to display deprecations collected from your unit tests][symfony-dept-phpunit]. So if you are deprecation free and follow the Symfony BC promise, you should be able to upgrade to a new major version without much pain.

## What version to use
So, you might be wondering: *What version should I use for my project?*

I recommend always using the latest release if you are working on a project. Doing so, you will have all the latest features, fixes and documentation at your disposal. This means that you will most likely never use an LTS release, which might seem counter intuitive.

My reasoning is that all fixes done in the LTS are merged into newer releases if the issue is present. Moreover, the LTS will reach its end of life at some point at which you need to upgrade anyhow. Upgrading earlier will help since not a whole lot is changed compared to the LTS appart from the BC layer. Also, you need to consider that there is two years between LTS releases. If you wait, you will need to do a lot of catching up when you decide to upgrade. The flipside is that new feature might have security vulnerabilities the LTS doesnt have. This is a common reason to use LTS releases. However, to me it does not weigh enough to justify it.

Also consider that Symfony is a framework, not an application or operating system. Whatever you made with Symfony, you are most likely continuing to develop and maintain. So sticking to an LTS release will prevent you from using the newer features. This seems to me the opposite of continues development and something you want to avoid.

So, when use an LTS? If you know your application is end of life and you only want to benefit from the security patches. Sticking with an LTS release might be your best option. You can still have a long support period with little effort. Any other situation and you are better off with the most recent release.

[semver]: http://semver.org/
[composer-caret]: https://getcomposer.org/doc/articles/versions.md#caret
[ubuntu-lts]: https://wiki.ubuntu.com/LTS
[nodejs-lts]: https://github.com/nodejs/LTS
[symfony-bcp]: http://symfony.com/doc/current/contributing/code/bc.html
[symfony-lts]: http://symfony.com/doc/current/contributing/community/releases.html
[symfony-choice-option]: https://github.com/symfony/symfony/pull/16849
[symfony-dept-logging]: http://symfony.com/blog/new-in-symfony-2-2-logging-of-deprecated-calls
[symfony-dept-phpunit]: http://symfony.com/blog/new-in-symfony-2-7-phpunit-bridge
