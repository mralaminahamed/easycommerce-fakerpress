👋 Hi and welcome to your plugin review ""!

Your plugin is not yet ready to be approved, you are receiving this email because the volunteers have manually checked it and have found some issues in the code / functionality of your plugin.

Please check this email thoroughly, address any issues listed, test your changes, and upload a corrected version of your code if all is well.

List of issues found


## WordPress.org directory assets in the plugin code.

We've detected WordPress.org directory plugin asset files in your submission. Thanks for including them; However, these files (banners, icons, screenshots created for the directory plugin page) are not part of the plugin code and should not be included in your plugin zip file.

Plugin assets should be uploaded to the WordPress.org repository separately after the plugin is approved. This is done through the SVN access.

For more information about plugin assets and how to upload them, please refer to: How Your Plugin Assets Work.

From your plugin:
05_14-17-19_easycommerce-fakerpress/assets/screenshot-5.png
05_14-17-19_easycommerce-fakerpress/assets/screenshot-9.png
05_14-17-19_easycommerce-fakerpress/assets/screenshot-8.png
05_14-17-19_easycommerce-fakerpress/assets/screenshot-10.png
05_14-17-19_easycommerce-fakerpress/assets/screenshot-6.png
05_14-17-19_easycommerce-fakerpress/assets/screenshot-1.png
05_14-17-19_easycommerce-fakerpress/assets/screenshot-2.png
05_14-17-19_easycommerce-fakerpress/assets/screenshot-3.png
... out of a total of 11 incidences.


## No publicly documented resource for your generated/compressed content

In reviewing your plugin, we cannot find a non-compiled version of your javascript and/or css related source code.

In order to comply with our guidelines of human-readable code, we require you to include the source code and / or a link to the source code, this is true for your own code and for developer libraries you’ve included in your plugin. If you include a link, this may be in your source code, however we require you to also have it in your readme.

https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#4-code-must-be-mostly-human-readable

We strongly feel that one of the strengths of open source is the ability to review, observe, and adapt code. By maintaining a public directory of freely available code, we encourage and welcome future developers to engage with WordPress and push it forward.

That said, with the advent of larger and larger plugins using more complex libraries, people are making good use of build tools (such as composer or npm) to generate their distributed production code. In order to balance the need to keep plugin sizes smaller while still encouraging open source development, we require plugins to make the source code to any compressed files available to the public in an easy to find location, by documenting it in the readme.

For example, if you’ve made a Gutenberg plugin and used npm and webpack to compress and minify it, you must either include the source code within the published plugin or provide access to a public maintained source that can be reviewed, studied, and yes, forked.

We strongly recommend you include directions on the use of any build tools to encourage future developers.

From your plugin:
build/admin.js:1  ...(()=>{"use strict";var e,t,n={160:(e,t,n)=>{var r=n(609),o="function"==typeof Object.is?Object.is:function(e,t){return e===t&&(0!==e||1/e==1/t)||e!=e&&t!=t},i=r.useSyncExternalStore,s=r.useRef,a=r.use...



## Generic function/class/define/namespace/option names

All plugins must have unique function names, namespaces, defines, class and option names. This prevents your plugin from conflicting with other plugins or themes. We need you to update your plugin to use more unique and distinct names.

A good way to do this is with a prefix. For example, if your plugin is called "Easy Custom Post Types" then you could use names like these:
function ecpt_save_post(){ ... }
class ECPT_Admin { ... }
update_option( 'ecpt_options', $options );
register_setting( 'ecpt_settings', 'ecpt_user_id', ... );
define( 'ECPT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
global $ecpt_options;
add_action('wp_ajax_ecpt_save_data', ... );
namespace vendor_name\plugin_slug;

Disclaimer: These are just examples that may have been self-generated from your plugin name, we trust you can find better options. If you have a good alternative, please use it instead, this is just an example.

Don't try to use two (2) or three (3) letter prefixes anymore. We host nearly 100-thousand plugins on WordPress.org alone. There are tens of thousands more outside our servers. Believe us, you’re going to run into conflicts.

You also need to avoid the use of __ (double underscores), wp_ , or _ (single underscore) as a prefix. Those are reserved for WordPress itself. You can use them inside your classes, but not as stand-alone function.

Please remember, if you're using _n() or __() for translation, that's fine. We're only talking about functions you've created for your plugin, not the core functions from WordPress. In fact, those core features are why you need to not use those prefixes in your own plugin! You don't want to break WordPress for your users.

Related to this, using if (!function_exists('NAME')) { around all your functions and classes sounds like a great idea until you realize the fatal flaw. If something else has a function with the same name and their code loads first, your plugin will break. Using if-exists should be reserved for shared libraries only.

Remember: Good prefix names are unique and distinct to your plugin. This will help you and the next person in debugging, as well as prevent conflicts.

Analysis result:
# This plugin is using the prefix "easycommercefakerpress" for 3 element(s).
# This plugin is using the prefix "ecfp" for 4 element(s).

# Looks like there is an element not using common prefixes.
class-easycommerce-fakerpress.php:31 class EasyCommerce_FakerPress


👉 Your next steps

Please, read this email thoroughly.

Take time to fully understand the issues we've raised. Review the examples provided, read the relevant documentation, and research as needed. Our goal is for you to gain a clear understanding of the problems so you can address them effectively and avoid similar issues when maintaining your plugin in the future.
Note that there may be false positives - we are humans and make mistakes, we apologize if there is anything we have gotten wrong. If you have doubts you can ask us for clarification, when asking us please be clear, concise, direct and include an example.

The new review process

Fix the issues in your plugin based on the feedback and your own review as we may not be sharing all the cases where the same issue happens. Use available tools like Plugin Check, PHPCS + WPCS, or similar utilities to help identify problems in your code.
Test your updated plugin on a clean WordPress installation with WP_DEBUG set to true.
⚠️ Do not skip this step. Testing is essential to make sure your fixes actually work and that you haven’t introduced new issues.
Go to "Add your plugin" and upload the updated version. You can continue updating the code there throughout the review process — we'll always check the latest version.
Reply to this email. Please be concise and do not list the changes — we will review the entire plugin again — but do share any clarifications or important context you want us to know.

ℹ️ To make this process as quick as possible and to avoid burden on the volunteers devoting their time to review this plugin's code, we ask you to thoroughly check all shared issues and fix them before sending the code back to us. I know we already asked you to do so, and it is because we are really trying to make it very clear.

While we try to make our reviews as exhaustive as possible we, like you, are humans and may have missed things. We appreciate your patience and understanding.

Review ID: R 05_14-17-19_easycommerce-fakerpress/15Aug25/T1 15Aug25/3.5
