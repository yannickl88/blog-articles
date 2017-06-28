[//]: # (TITLE: Mutations in data over time)
[//]: # (TAGS: entities, data)

When dealing with entiprise software, your data is often the most valueable part. Without it you have no custormers and usally no income. So what are you doing to make sure the data is being dealt with correctly? 

How will you trace back errors in your data years after the fact? This is something you need to think about when designing your software. There are many aproches in doing so but I found that I often try to make my data immutable. This forces you to think differently about your data and what the impact of an update will be.

## Common use-cases
