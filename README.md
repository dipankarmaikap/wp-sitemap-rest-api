# WP Sitemap Rest Api Plugin

This plugin adds REST API end points for generating sitemap for your headless wordpress site.

> Using this plugin? I would love to see what you make with it. 😃 [@maikap_dipankar](https://twitter.com/maikap_dipankar)

## Quick Install

- Clone or download the zip of this repository into your WordPress plugin directory & activate the **WP Sitemap Rest Api** plugin

## Find this useful?

<a href="https://www.buymeacoffee.com/dipankarmaikap" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 40px !important;width: auto !important;" ></a>

## Usage

This plugin adds 4 end points to wp rest api

### Get total number of posts, pages, authors, tags and categories.

```javascript

import axios from "axios";

export default async function getTotalCounts() {
  const res = await axios.get(
    `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/wp-json/sitemap/v1/totalpages`
  );
  return (await res?.data) ?? {};
}

```

This will then give you a result as such:

```json
{
    "totalCategories": 1,
    "totalTags": 0,
    "totalPublishedPosts": 15,
    "totalPublishedPages": 1,
    "totalUsers": 1
}
```
### Get author urls
This gives you two option to add page no and how many items you want in one request.

```javascript

import axios from "axios";

export default async function getAuthorUrls() {
    const res = await axios.get(
      `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/wp-json/sitemap/v1/author?pageNo=${page}&perPage=${sitemapPerPage}`
    );
    return (await res?.data) ?? [];
}


```

This will give you a result like below:

```javascript
[
  {
    "url": '/author/dipankarmaikap',
  },
  {
    "url": '/author/willsmith',
  }
]
```
### Get taxonomy urls
This gives you two option to add page no and how many items you want in one request like the above + it gives you the option to add the taxonomy type (eg: category or tag)

```javascript

import axios from "axios";

export default async function getTaxonomyUrls() {
    const res = await axios.get(
      `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/wp-json/sitemap/v1/taxonomy?pageNo=${page}&taxonomyType=${type}&perPage=${sitemapPerPage}`
    );
    return (await res?.data) ?? [];
}


```

This will give you a result like below:

```javascript
[
  {
    "url": '/category/news',
  },
  {
    "url": '/category/australia',
  }
]
```

### Get post or page urls
This gives you two option to add page no and how many items you want in one request like the above + it gives you the option to add the post type (eg: page or post)

```javascript

import axios from "axios";

export default async function getPostsUrls() {
  const res = await axios.get(
    `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/wp-json/sitemap/v1/posts?pageNo=${page}&postType=${type}&perPage=${sitemapPerPage}`
  );
    return (await res?.data) ?? [];
}


```

This will give you a result like below:

```javascript
[
  {
    "url": '/blog/hello-world',
    "post_modified_date": "14 April 2022"
  },
  {
    "url": '/blog/how-to-create-a-wp-plugin',
    "post_modified_date": "14 April 2022"
  }
]
```

## Contributions

Contributions are welcome :). This was a very quick build.
Feel free to make a PR against this repo!

[Open an issue](https://github.com/dipankarmaikap/wp-graphql-image-dataurl/issues)

[@maikap_dipankar](https://twitter.com/maikap_dipankar)
