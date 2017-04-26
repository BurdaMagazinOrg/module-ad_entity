# Advertising Entity

A Drupal module which provides consolidated management for various types of
advertising instances.

# Requirements

- The <a href="https://www.drupal.org/project/entity">Entity API</a> module.
- The <a href="https://github.com/BurdaMagazinOrg/module-theme_breakpoints_js">
Theme Breakpoint JS</a> module.

# Quick start

- Install this module.
- You need at least one further module which defines an Advertising type.
  The ad_entity_adtech module for example defines advertisement provided by
  AdTech Factory. This module can be found in the 'modules' subfolder. 
- Configure global settings for Advertising entities
  at admin/structure/ad_entity/global-settings.
- Create and manage your Advertising entities at admin/structure/ad_entity.
  When your theme has multiple breakpoints, you can create one entity for each.
- Once you've created your entities, create and place an Advertising block at
  admin/structure/block. The Advertising block enables you to define the
  Advertising entities to display, optionally for each device variant.

# About view handlers

For each advertising type, there are usually two view handlers to choose from:
 - A default view, which is the recommended way for viewing the ads on a page.
 - An iframe, which should be used e.g. for embedding ads on external platforms
   like Facebook Instant Articles.

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
choose 'Context from entity only'.

If you also want to additionally deliver the context
being attached to terms which belong to a node, choose
'Context from node with taxonomy (without trees)'. To include the taxonomy tree,
you can additionally choose between the 'tree aggregation'
or 'tree override' formatter variants.

Using tree aggregation means that all contexts from a term's ancestors
will be included. Please note that such operation could be expensive.

Using tree override means that the first context found in the taxonomy tree
will be used, in case the given term has no context defined by itself.
The first ancestor of the erm having a context will be used (bottom-up).
Please note that this operation could be expensive as well.

For taxonomy terms, you can use tree aggregation or tree override as well.

# Tips

It's recommended to always display your Advertising entities through
Advertising blocks. This way, you're able to change your advertisement
on your whole site and switch between available variants of advertisement.

The default tree aggregations and tree overrides can be expensive operations.
When using a lot terms for nodes with large trees, it's recommended to
write your custom formatter instead, which directly loads the context you want.
