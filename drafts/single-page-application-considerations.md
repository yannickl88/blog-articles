[//]: # (TITLE: Single Page Application considerations)
[//]: # (DATE: 2016-09-15T08:00:00+01:00)
[//]: # (TAGS: spa, browser, javascript)

With the rise in mobile computing power and faster browser the internet has seen some change. Where websites used to be simple pages we now have a rich user experiences and mobile apps. These complex sites are usually called Singe Page Applications (SPA). No longer are sites separate pages like a book but behave more like desktop applications. They can better cater to their target audience because they provide more flexibility. Yet, all this comes at a cost. The question becomes: Is the web better for it? 

In this blog post I will try to motivate why you should reconsider when you are thinking of creating a SPA. Not everything should be a SPA and some serious downsides which you need to consider. 

## History of the Single Page Application 

The history of the single page application can be traced back to the mid 2000. At that time browsers were starting to improve their javascript and rendering engines. The boost in performance for client side computing lead companies to develop web-based applications. Such applications like Gmail, which launched in April 2004 lead the way. No longer did you need to install a separate program to access your content, all you needed was a browser. A major change was AJAX. This technique allows sites to loads data asynchronously, instead of loading a whole page. AJAX allows for more application-like websites, because updates maintain state. Much like a traditional desktop application. 

Another advantage of having a web-based application is compatibility. As long as your browser could run the page, you could view it regardless of device. With more and more powerful mobile devices at our disposal, we see a shift to responsive designs. The content adapts to the the screen size of your device and you can use the application anywhere. 

This is where the SPA shines. Since you have some logic before actually rending anything, you can make slight adjustments. You have more control over what to show to the end-user at the client and updating only when needed. This provides the most control over the UX and improve performance, *if done right*. With the shift to a modern web we might have lost something along the way. 

## A narrowing mindset 

A famous quote: *"I suppose it is tempting, if the only tool you have is a hammer, to treat everything as if it were a nail."*. [Abraham Maslow said this][gbooks-abraham] in 1966 referring to the way science approaches problems. What it means is that if you have a familiar solution you will try to apply this to everything. It might even prevent you from trying something else which might be more effective. The same is true for most developers, they tend to gravitate to the tools and frameworks they use most often. I too am guilty of this, you think of a solution in the language or tool you are most familiar with. Yet, while this behavior is common, I think we can all agree it is not always a desirable trait in a good programmer. 

This brings me back to SPAs, it should not be a tool you use for everything, only for things where it is applicable. This is something that bothers me most with the modern web. There is a time and a place for using a SPA and similar, there is a place for the traditional website. Your job as a programmer should be to choose the tools and solutions which yield the best results. You do this by weighing the pros and cons of a tool against each other and select which solution first best. Deciding that you will want to build a SPA should be a calculated choice, the same as any other. 

## Things to consider when developing a SPA 

I think most of us are aware of the advantages of using a SPA like website. If not, you can read [The Single Page Interface Manifesto][spi-manifesto]. While rose colored, it provides some good insights into the advantages of a SPA. Even this manifesto glosses over a somewhat large disadvantage: SEO. They provides an *easiest* way to fix this: *"...offer two different navigation modes, SPI [Single Page Interface] for end users, pages for web crawlers"*. To me, that means I have to support both, fix bugs in both and build the same logic in both. Which seems like a lot of work, why not just serve the crawler pages to your end-users?

This does not mean indexing of a SPA cannot work. As early as 2008 the google bot could run JavaScript albeit limited. Today the google bot can do a lot more, [even index a SPA][searchengineland-spaseo] to a certain extend. That said, the support for traditional websites will be a lot better. One reason is because Google has been around since 1997. There is just more experience crawling traditional sites than a SPA. Another reason is because of the dynamic nature of JavaScript. There are a lot of ways to achieve the same goal, and the crawler might not support all. 

SEO is a problem for the site owner, if a user found your site it is no longer important. What bothers me most is that most SPAs do not play well with my browser features. [Stefan Tilkov writes about his frustrations with using SPAs][stilkov-spa] and I think he is right about most of the things. Your SPA should support bookmarks so I can link to a specific part. It should also respect back and forward buttons, pressing back should not send me back to Google. Your site should be able to refresh and still work, not send me back to the home page. 

To do all those things you have to build all those features and support those features. This will take extra development resources that you need to justify using while developing. Compared to a more traditional site, these features work out of the box thanks to the browser. 

In [a talk from Paul Irish][youtube-paulirish] he discusses optimizing your page and their load times. This is important to consider when building a SPA, since most of the time you will be serving it as one big file. Loading your application will most likely take longer than with a regular website. Most sources claim that you should load your page within at least 2 seconds. After that, user will actually start leaving your site. So with a SPA you have to be mindful of how much JavaScript you want to serve. Too much and the user will lose interest and too few and you might love the fluidity of having a SPA. Yet, most sites chose to serve everything upfront and you will end up with large JavaScript files. Deciding to do so will depends how willing the user is to wait before he can use your site. 

## Wrapping up 

Is there a place for SPAs on the web? Well yes of course, but like any choice you make when developing something you have to choose well. SPAs are great at creating application like websites like a mail client. These sites are utilitarian by nature, meaning they are tools for do some task like reading mail. However, if you have a product you want to showcase, a SPA might actually do more harm than good. 

So again it comes down to selecting the right tool for the job. You can go for a full SPA and accept the limitation to SEO and browser feature support. On the other end, you can go for the more traditional site where everything is handled server side. Yet, this spectrum is not binary, all sorts of solution fall somewhere between those. It is up to you as a developer to decide what to use and when.

## Examples
I would like to provide some examples which showcase good and bad usages of SPA.

### http://knockout-spa.mybluemix.net/

While I get the whole *eat your own dog food* approach to the site. It seems odd to use a SPA for your product showcase and documentation. Both cases you are serving static HTML. Moreover, Google has indexed nothing of the site, which seems as such a waste.

### https://www.ryanair.com/

That loading icon reveals all does it? The time until a page load is around 2 seconds, which is a lot it I am honest for a page that shows little.

### https://analytics.google.com/analytics/
A useful tool that I do not mind waiting for. I do not think Analytics would needs SEO but they do provide deep linking using the URL hash. This is a good example of a tool or application instead of a website.

### https://app.vwo.com/

Again, while there is a loading screen here it makes sense to use this as a SPA. There is a lot of data to keep track of, doing page reloads would be a waste here.

[gbooks-abraham]: https://books.google.nl/books?id=3_40fK8PW6QC&lpg=PP1&pg=PT21#v=onepage&q&f=false
[spi-manifesto]: http://itsnat.sourceforge.net/php/spim/spi_manifesto_en.php
[stilkov-spa]: https://medium.com/@stilkov/why-i-hate-your-single-page-app-f08bb4ff9134#.m3skk8as1
[searchengineland-spaseo]: http://searchengineland.com/tested-googlebot-crawls-javascript-heres-learned-220157
[youtube-paulirish]: https://youtu.be/R8W_6xWphtw
