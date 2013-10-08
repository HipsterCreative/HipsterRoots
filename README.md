HipsterRoots
============

HipsterRoots is a starting WordPress theme based on [Roots](http://roots.io). It integrates many of HipsterCreative's tools for building Wonderful Wordpress Works, including template debugging output and a rediculously awesome [Mustache](http://mustache.github.io/) templating system.

**The documentation is in progress**, please keep an eye here for updated information or contact us for help.

##Studio Branding (aka, Sub Footer)

Here at HipsterCreative, we like to help our community and local non-profits with discounted or pro-bono work. In exchange we ask that those clients allow us to spotlight ourselves with a piece of branding at the very tail of the their site. This looks something like this:

![HipsterCreative sample Studio Branding](http://f.cl.ly/items/1X1a2o313K0W18360O3L/Screen%20Shot%202013-10-08%20at%204.31.06%20PM.png)

This is injected into the theme via the **wp_footer** action, so it should show up nicely at the end of the page. There are two places to handle this. To enable or disable the feature, edit this file:

    ./hipster_roots/_php/_main.php
    
Towards the top, you will edit the **$show_footer** boolean like so:
 
    private $show_footer = true;
        
Additional logic and HTML for output is located at:

    ./hipster_roots/_php/_footer.php
    
Add your own HTML between the **ob_start()** and **ob_stop()** functions. You will note that in our sample we used inline styles. We also base64 encoded our image assets. This was done to make the Studio Branding entirely self contained.