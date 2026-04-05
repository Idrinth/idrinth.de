# Blogging Experience - Or why i built a blog from scratch

As you might have noticed, this is a static site for the most part, containing HTML, CSS and a tiny amount of javascript.

The backend is json and md files, with pretty much everything generated from these during built time, so that viewing the content is fast.

## What is missing so far and what are my plans?

- Comments: I intentionally left the comments out, so i don't need to do regular moderation on them and check them for potential harmful content
- Tag-Search: This will be coming, I ust don't see it as relevant yet
- Open-Source: This blog is currently not open-source. I wonder if i should change that, given there are potentially more people who like easy solutions for blogs
- RSS and ATOM: the feeds are being built right now, they'll be available in multiple flavors, one for all post and one for each category, so you can follow the interesting parts of your choice

If anything else is missing, let me know, I'd be happy to think about adding more!

# Why not Wordpress or other blod systems

There are multiple reasons, but it mostly comes down to overhead. I can write HTML or Markdown very easily and don't care about most features these tools bring along. On the other end I end up with somebody else's code running on my server and potentially have risks that I can not predict.

The simple solution is a few lines of PHP, a few lines of HTML and no issue with third party libraries.
