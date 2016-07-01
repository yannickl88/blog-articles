[//]: # (TITLE: Bloginception)
[//]: # (DATE: 2016-07-01T09:00:00+01:00)
[//]: # (TAGS: php, blog, symfony)

I think the need to blog for a programmer is something inherent. It's a desire to spread knowledge, and create a post is something everyone can read. So it's a bit strange, at least to me, that there are not more blogging platforms targeted at developers. A quick Google search either results in Wordpress plugins, NodeJS HTML generators or CMS solutions.

This happened more often if I have to believe this twitter post: https://twitter.com/iamdevloper/status/743799765566054410. I think there are more developers which create their own blogging software and then blog about it, like... This post. Then again, it shows that there is no proper solution for software development blogs yet.

# Github as content management
A quick brainstorming session later with Iltar, we thought we could do better. Something that is build with versioning in mind, flexible, open-source and very low maintenance. We came up with an idea to use Github as version management and just create some software that aggregates everything. Because blog posts are nothing more than just pieces of text, and why not version it with a VCS like git.

We decided that each author should just have its own repository where they manage their posts. Using webhooks we can tell the blog that something has changed. All we have to do is pull the repository and index the new changes. This enables the authors to use the Github eco-system for creating blog post, like the Github editor. Alternatively, an author can also just use the IDE of choice which can support markdown. It also allows for open-source like nature of not only the code but also the post, allowing even for other people to contribute to posts and gives the freedom to write like you want. 

# Standing on the shoulders of giants
As with Github, we try to leverage other technologies. We do the same for parsing markdown. Instead of trying to write our own, we use the [erusev/parsedown][parsedown] package. This in combination with [Prism.js][prism] we have a way to write code snippets in posts with syntax highlighting.

We do the same with profile information, we simply use [Gravatar][gravatar] for the avatars instead of hosting them ourselves.

# What did we write
So, you might be wondering, what did we actually write? Of course, we made some software to glue all these packages together. We created a simple `Symfony` application which we use for the handling of web requests and rendering the pages. It also allows us to use the `Symfony` routing to create links to pages.

We also wrote a bit of software to manage and maintain the linked git repositories. This is able to update a repository to the latest version and index where all the files are. Using this, we can update blogs when changes occur. These can be triggered by the [Github Web-Hooks][github-webhooks].

If you are interested, the blog software is fully open source. You can browse it at https://github.com/yannickl88/blog.

[parsedown]:https://github.com/erusev/parsedown
[gravatar]:https://gravatar.com/
[prism]:http://prismjs.com/
[github-webhooks]:https://developer.github.com/webhooks/
