# wp-settings-api
This repository is an override from [tareq1988/wordpress-settings-api-class](https://github.com/tareq1988/wordpress-settings-api-class) with some new features

## Features
* **Subsection field**
* **No padding on submit button**

## Usage
Instead of initializing as `new WeDevs_Settings_API` like the original library, do it like:
`new \ThanksToIT\WP_Settings_API\Settings_API()`;

## Package Installation (via Composer)
To install this package, edit your `composer.json` file:

```js
{
  "repositories": [  
    {
      "type": "vcs",
      "url": "https://github.com/thanks-to-it/wp-settings-api"
    }
  ],
  "require": {
    "thanks-to-it/wp-settings-api": "dev-master"
  }
}
```

And don't forget to include the original library as well `composer.json` file:

```js
{
    "require": {
        "tareq1988/wordpress-settings-api-class": "dev-master"
    }
}
```

## Subsection field
A new subsection input type allowing to create a Subsection
```php
array(
  'name'  => 'subsection_general',
  'label' => __( 'My subsection', 'domain' ),
  'desc'  => __( 'Now we can have a subsection description too ;)', 'domain' ),
  'type'  => 'subsection'
),
```
![Subsection field](https://i.imgur.com/M28opH7.png)

## No padding on submit button
On the original class, there is a anoying `style="padding-left: 10px"` on submit button.
No more
