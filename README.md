# WordPress Tags-Like Meta Box

The WordPress tags meta box has a great interface for dealing with data. 
This helper class will allow you to customize a tags-like meta box of your own with ease.


##Use
Include the class file anywhere (in your plugin or theme), and then instantiate the class.

    $myVersionOfTags = new Tags_Like_Meta_box( array( 'ID' => 'post-notes', 'nice_name' => 'Notes' );

By default the meta box is added over all post types, and saves any data into a piece of post meta for the post. 

If you want to edit the capabilities, just create a subclass and edit away!

