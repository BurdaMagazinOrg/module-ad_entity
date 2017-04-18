# Advertising Entity

A Drupal module which provides consolidated management for various types of
advertising instances.

# Requirements

- The <a href="https://www.drupal.org/project/entity">Entity API</a> module.
- The <a href="https://www.drupal.org/project/breakpoint_js_settings">
Breakpoint JS settings</a> module.

# Quick start

- Install this module.
- You need at least one further module which defines an Advertising type.
  The ad_entity_adtech module for example defines advertisement provided by
  AdTech Factory. This module can be found in the 'modules' subfolder.
- Configure your JS breakpoints on admin/config/system/breakpoint_js. 
- Configure global settings for Advertising entities
  at admin/structure/ad_entity/global-settings.
- Create and manage your Advertising entities at admin/structure/ad_entity.
  When you have different settings per client device variant
  (smartphone / tablet / desktop), you can create one entity for each variant.
- Once you've created your entities, create and place an Advertising block at
  admin/structure/block. The Advertising block enables you to define the
  Advertising entities to display, optionally for each device variant.

# About Advertising contexts

A given Advertising context is able to extend or manipulate the information and
defined behavior for Advertising entities being displayed on a page.

Advertising context can be defined by content such as nodes and taxonomy terms.

To enable users defining contexts, the 'Advertising context' field must be
attached to an entity type. Choose unlimited cardinality to let users add
multiple contexts.

The field provides different formatters for the Advertising context.
In the 'Manage display' section of your entity type, place the context field
into the content region to deliver the user-defined context on the page.

The available field formatters differ in which context will be delivered.
To just deliver the user-defined context from the given entity,
choose 'Context only from entity content'. If you also want to automatically
deliver the context being attached to terms belonging to the content,
choose 'Context from node including its terms (without trees)' or
'Context from node including taxonomy tree aggregation"'.

For taxonomy terms, you can also use the tree aggregation formatter.

Using tree aggregation means that all contexts from a term's ancestors
will be included. Please note that such operation may be expensive.
When using a lot terms for nodes with large trees, it's recommended to
write your custom formatter which directly fetches the contexts you need.

# Tips

It's recommended to always display your Advertising entities through
Advertising blocks. This way, you're able to change your advertisement
on your whole site and switch between available variants of advertisement.
