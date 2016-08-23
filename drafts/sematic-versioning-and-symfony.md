Versioning is more than just slapping an incrementing number on your new release. A good version increase will let the users of your package know more than just that there is a new release. Most of us will understand that there is a difference between different major versions and different minor versions. However, using a proper versioning scheme can help people a lot with inferring what the impact of the upgrade will be.

In this post will try to give some context to the Symfony versions and what you can expect of each increment, be it a patch, minor or major version bump.

## Semantic versioning
First of all, Symfony uses [semantic versioning][semver] for determining new release numbers. This means that each version of Symfony will be build up of three numbers, separated by a dot, i.e., `2.8.1` or `3.1.3`. Starting from right to left we first have the least significant number. This is the patch version. An increment in patch versions (e.g., `3.1.2` to `3.1.3`) will mean that a bug was fixed or some behavior corrected. These changes should always be backwards compatible and will never introduce new features. The idea is that downgrading to a lower patch version should never break your application using the package since you could not have been using something that is no longer there.

The next number is the minor version, an increment in minor versions (e.g., `3.0.9` to `3.1.0`) will mean that the public API was extended. These changes should always be backwards compatible but may introduce new features. This means that downgrading to a lower minor version might break any code depended on the newly introduced feature. Thus it is recommended to put your lower version bound on a minor (since downgrading patches won't matter). This will make your version constraints just lose enough to allow for better compatibility between packages.

Lastly, we have the major version, an increment in major versions (e.g., `2.8.9` to `3.0.0`) will mean that the public API was changed and not backwards compatible. These changes are often re-writes of the codebase or even removal of old code.

This means that based on the release number you can tell what the impact of the change will be to your application if upgraded. Most likely you will want to have an upper bound on the major version and a lower on the minor. For example, using the composer syntax that would be `>=3.1.0 <4.0.0` (or [shorthand][composer-caret] `^3.1`); Meaning you want the API of the `3` major version with a feature from `3.1.0`.

## Long-Term-Support (LTS) releases
Next to the versions, most software also has LTS releases (mostly a specific version marked as LTS). Think of [Ubuntu][ubuntu-lts] or [Node.js][nodejs-lts]. These are special releases which for the basis for a long support period. These releases will be supported for a longer period, meaning it will recieve patches for bugs and security fixes. However, for the most part these releases do not get additional features. For enterprise usages these versions are the most stables because they give a certain guarantee they will work for the foreseeable future.

The way a company usually works is that once a release is created that is also a LTS, this will become a fork of the code base. This version of the software is usually maintained next to the newer versions and any fixes are backported to the LTS fork. The same is done for Symfony.

## Symfony releases and LTS versions
To quote the [Symfony docs][symfony-lts]:
> In the Symfony `2.x` branch, the LTS versions are `2.3`, `2.7` and `2.8`. Starting from the `3.x` branch, only the last minor version of each branch is considered LTS (e.g. `3.4`, `4.4`, `5.4`, etc.). 

Seeing as the current release is `3.1.3` there are at the time of writing three LTS releases which, from the moment of release, will receive updates up to 36 months. So for the `2.8` versions, this will be until November 2018.

From `2.8` and onwards, when an LTS is released, so is the next major version (`3.0` in this case). The way the Symfony codebase works is that once a refactor which is not backwards compatible is done a migration period must be present. That means that the code must be backwards compatible; however parts of the code must be deprecated which will be removed. Once the new major is released the backwards compatibility is removed.

## Backwards compatibility promise
Finally, Symfony also has a [backwards compatibility promise][symfony-bcp]. What this means is that on top of what you can expect of the semantic versioning, they try to make migrations as smooth as possible.

For example: A new way the `ChoiceType` worked upgrading from `2.x` to `3.x` was introduced. Starting from `3.0.0` [the keys and values of the choices are swapped][symfony-choice-option] which would mean that you have to change all the code using choices types. Doing all this in one commit might mean a lot of changes and possibly forgetting some breaking your application. To make this transition smoother, in version `2.7.0` the option `choices_as_values` was introduced, which, when enabled, makes the choices behave as the `3.0.0` code. This allows you to gradually change all your code before upgrading and doing so in smaller changes.

To make these transition more easy, starting from `2.2` [the deprecations are logged in the Symfony developer toolbar][symfony-dept-logging]. Moreover, [there is a special PHPUnit bridge to even log deprecations in your unit tests][symfony-dept-phpunit].

So if you are deprecation free and follow the Symfony BC promises, you should be able to safely upgrade to a new major version.

## What version to use
So, you might be wondering: *What version should I use for my project?*

Personally, I recommend always using the latest release if you are working on a project regardless of what you are using it for. You will benefit from all the latest features, fixes and documentation. This means that you will most likely never use an LTS release, which might seem counter intuitive.

My reasoning is firstly, that all fixes done in the LTS are usually backports from newer releases. Moreover, the LTS will reach its end of life at some point at which you need to upgrade anyhow. Doing so earlier will help since major not a whole lot of changes were made between the LTS and the following major. Also, you need to consider that there is two years between LTS releases, so when upgrading you need to do a lot of catching up. Downside is that new feature might have security vulnerabilities which are not present in the LTS (a common pro of LTS releases).

Secondly, Symfony is a framework, not an application or operating system. This means that whatever you made with Symfony, you are most likely continuing to develop and maintain it. Sticking to an LTS release will prevent you from using the newer features which seems to me the opposite of continues development.

The only reason to use an LTS is that if you know your application is end of life and you only want to benefit of the patches. Thus sticking with an LTS release might be your best option. Then you can still benefit from a long support period with very little effort. Any other situation and you are better off with the most recent release.

[semver]: http://semver.org/
[composer-caret]: https://getcomposer.org/doc/articles/versions.md#caret
[ubuntu-lts]: https://wiki.ubuntu.com/LTS
[nodejs-lts]: https://github.com/nodejs/LTS
[symfony-bcp]: http://symfony.com/doc/current/contributing/code/bc.html
[symfony-lts]: http://symfony.com/doc/current/contributing/community/releases.html
[symfony-choice-option]: https://github.com/symfony/symfony/pull/16849
[symfony-dept-logging]: http://symfony.com/blog/new-in-symfony-2-2-logging-of-deprecated-calls
[symfony-dept-phpunit]: http://symfony.com/blog/new-in-symfony-2-7-phpunit-bridge
