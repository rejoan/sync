# sync
A WordPress plugin work with backend & frontend CPT.

- Backend: get API data throug ajax and create CPT GRID
- Frontend: Display "apid" CPT based on shortcode with templete selection facility
- Frontend: get business info by shortcode

## Short codes
- Example1: [random-business-list view=2 num=20] get 20 record for apid CPT with template 2
- Example2: [al-businessinfo] get business info from API in any custom post type
- Example3: [al-reviews-display] get reviews of a CPT from API and display slick slider
- Example4: [al-map-display] get map of address for the post
- you may make a child theme of twenty twenty four with single-apid.html template and use example 2,3 shortcode

## Requirements
1. First clone the repo
```
git clone git@github.com:rejoan/sync.git && cd sync
```
2. Then install the plugin and activate

3. In wp-config.php put your map key for shortcode [al-map-display]

```
define('API_KEY_GOOGLE', 'google_map_api_key');
```
4. In syncData.php replace API end point
```
https://api.prospectbox.co
```


### Authors

👤 **Rejoanul Alam**

- Github: [@githubhandle](https://github.com/rejoan)
