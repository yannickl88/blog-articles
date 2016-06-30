[//]: # (TITLE: Bloginception)
[//]: # (DATE: 2016-05-20T13:46:00+01:00)
[//]: # (TAGS: php, blog, symfony)

I think the need to blog for a programmer is something inhert. It's a desire to spread knowledge, and create a post is something everyone can read. So it's a bit strange, at least to me, that there are not more blogging platforms targeted at developers. A quick google search either results in Wordpress plugins, NodeJS HTML generators or CMS solutions.

# Github as content management
A quick brainstorming session later with Iltar, we thought we could do better. Something that is build with versioning in mind, flexable, open-source and very low maintenaince. We came up with an idea to use Github as version management and just create some software that aggreagtes everything. Because blog posts are nothing more than just pieces of text, and why not verion it with a VCS like git.

We decided that each author should just have its own repository where they manage their posts. Using webhooks we can tell the blog that something has changed. All we have to do is pull the repository and index the new changes. This enables the authors to use the Github eco-system for creating blog post, like the Github editor. Alternativly, an author can also just use the IDE of choice which can support markdown. It also allows for open-source like nature of not only the code but also the post, allowing even for other people to contribute to posts and gives the freedom to write like you want. 

# Standing on the sholders of giants
As with Github, we try to leaverage other technologies. We do the same for parsing markdown. Instead of trying to write our own, we use the [erusev/parsedown](https://github.com/erusev/parsedown) package. This in combination with [Prism.js](http://prismjs.com/) we have a way to write code snippets in posts with syntax highlighting.

We do the same with profile information, we simply use [Gravatar](https://gravatar.com/).

# What did we write
So, you might be wondering, what did we actually write? Ofcourse, we made some software to glue all these packages together. We used a simple `Symfony` application which we use for the handling of web requests and rendering the pages. It also allows us to use the `Symfony` routing to create links to pages.

We also wrote a bit of software to manage and maintain the linked git repositories. This is able to update a repository to the latest version and index where all the files are. Using this, we can update blogs when changes ocure. These can be trigged by the [Github Web-Hooks](https://developer.github.com/webhooks/).

If you are interested, the blog software is fully open source. You can browse it at https://github.com/yannickl88/blog.
