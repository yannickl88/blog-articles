[//]: # (TITLE: Natural Language Parsing)
[//]: # (DATE: 2016-08-01T09:00:00+01:00)
[//]: # (TAGS: natural language, machine learing)

Natural language parsing is slowly becomming more and more implemented in daily application. Just look at Apple's Siri, Microsoft's Cortana and Google's Google Now. All have some form of processing spoken user input in the form of regular sentences and question. So how can you implement such a system yourself?

Well, depends what you want to do really. As I see it, there are two approces for doing this, each with it's own set of dificulties. One is the more classical one, trying to annotate each word with their meaning (i.e., noun, adjetive, etc.) and from that try to understand meaning. This is what I refer to as the linguistic approch and is the most diffucult one to implement right. However, if done right: you should be able to actually understand what a user is asking or telling and possibly generate sentences.

The other way is less elegant and less versatile but depending on what you want, it just as powerfull and far less complicated to implement. This uses a machine learning approch to see what the user was saying and formulate pre-programmed replies. It relies on a training input set with examples of phrases and checks agains that to see if the input somewhat resembles those. This can be used to create simple chat bots are help systems. This version of Natural Language Parsing is what I will focus on in this post.

## Machine Learning
So first of all, what is machine learing? [Wikipedia][wiki-def] has the following definition: "Machine learning explores the study and construction of algorithms that can learn from and make predictions on data.". For natural language processing, this comes down to creating algorithms which, given a users input, can make predecitions what intent of that sentence was.

So how do you do machine learning. Well there are a couple of well documented and researched algorithms. The one I prefer as a staring point is usally the Naive Bayes classifier. This one is simple to implement and can give pretty good results compared to more advanced algorithms.

## NLP + ML
Natural Language Parsing and Machine Learning can thus go hand in hand if you are only interested in the intent of a user rather than the exact meaning. So for example: with have the following training set:

| String | Type |
|---|:---:|
| How are you? | `mood` |
| How do you do? | `mood` |
| What time is it? | `time` |
| What is the time? | `time` |

If the user would ask "Give me the time", we can check our training set and can conclude that this input mostly resambles the `time` type. We didn't try to parse the input at all, all we did what see what looked most similair from our training set.

And this is what makes Machine Learning really powerful if you only want to know the intent of a user. Of course you can extend this further when you know the intent, check for specific string to further understand the input. Like dates or places.

And in essence, the better your training set, the better you can match the user input to that training set. However, some limitations are that if the user inputs something that wasn't in the training set, you might get unexpected results. So usually it's a good thing to have a confidence level (e.g., how certain you are that it's a type) or some `none` type with random input.

This method of Natural language parsing is the core of the [Microsoft Bot Framework][ms-bot-framework] and their [Luis][ms-luis] service. And for a shameless plug, I also made a [similair java package][yannickl88-natural-language] which uses Naive Bayes to geuss the user intent. 

Machine learing for Natural Language Parsing can be a very powerful tool for creating smart chat bots or help systems, which - [as Microsoft sees it][ms-vision] - can be the future of how we interact with the digital world.

[wiki-def]: https://en.wikipedia.org/wiki/Machine_learning
[ms-bot-framework]: https://dev.botframework.com/
[ms-luis]: https://www.luis.ai/
[ms-vision]: http://www.theverge.com/2016/3/30/11331388/microsoft-chatbots-ai-build
[yannickl88-natural-language]: https://github.com/yannickl88/natural-language
