# A question of databases

I read quite a few posts in X recently, that were promoting single database applications. I am concered reading this, as this is the generation coming after me falling for the same issue the generation before me did: One System To Solve Everything.

## How do databases differ?

Databases store data, that is their defining factor. Different databases store different data in different ways though and perform better when you use them for what they were built.

Relational data for example is usually solved by an sql database, the reason being that it's indexing mechanisms are optimised for exactly the kind of few-field-tuple related to few-field-tuple datasets.

MongoDB and many other NoSQL databases instead store documents of sorts. A document is defining one single way data is intended to be consumed by it's structure. You can represent relational data in it, but it is slower and takes more effort, so rather store data in it that is hierachical, a document with it's pages for example.

Vector databases are another type entirely again. They store data as raw material, but their benefit is not that you can retrieve that data by id, but that you can find things that are logically related, not structurally. This is the default database type for all AI products that index data instead of dumping everything into every request.

Caches and Key-Value-Stores are the fourth big type. They have a tiny featureset, but bring speed that other databases usually can't match for what they do: read our a specific value at a specific, given id. Redis is a classic example here, used widely for data that doesn't have to persist or is costly to generate on the fly.

## Do I need that for my 100 User Chatbot?

Likely not, because 100 users is not a big amount of data. Their chat data and metadata will be tiny enough to process with almost any system, even file based ones. The issue is properly scaling, you don't want to run into a point where nothing works because your database is overloaded doing a thing it's not good at, just because you skipped finding the right tol for the job a few months or years earlier.

## Are there any examples of multi database systems?

Almost all closed source system I have touched was using 2-3 databases minimum, usually a mysql and a redis-like cache at the core. For Open-Source I have [Wolfgang AI](https://github.com/bjoern-buettner/roleplay-ai) that uses a lot of databases to minimize loading and data processing times. Might not be prefect either, but if you find something to improve let me know!
