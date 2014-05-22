Cricket
=======

Cricket is a newly open sourced, yet very rich and mature PHP web application framework.  Cricket has been used for more than 5 years on internal projects and we are happy to return some love to the community by sharing it with the world.

Cricket applications are built with Page and Component classes that are analogous to a desktop application's Window and View hierarchy.    Cricket manages your state intuitively using just the instance variables in your Page and Component classes and allows your rendered HTML to directly link to (call) your component and page action methods.

Each page and component class can be bundled with its own resources (templates, images, js, css, etc.), and a component can render itself independently of the page and all other components. This independence allows pages and components to be truly sharable and reusable. 

Please consult the WIKI for more details.

### A simple example

Consider the following example of a simple page that contains a single component.  This component displays a current "count" number and offers methods to increment or decrement the number.

The key things to make note of are:
* The Page and Component classes are separate from the HTML layout (Controller and View of MVC)
* The Page class adds the component as a child
* The Component has state that is managed by cricket (instance variables)
* The Component's template links directly to action methods in the Component class


First, lets look at the Page and Component classes
```php
use cricket\core\Page;
use cricket\core\Component;


class CounterPanel extends Component {
    public $count = 0;
    
    public function render() {
        $this->renderTemplate("_counter_panel.php",array(
            'count' => $this->count,
        ));
    }
    
    public function action_increment() {
        $this->count++;
        $this->invalidate();
    }
    
    public function action_decrement() {
        $this->count--;
        $this->invalidate();
    }
}


class PageExample extends Page {

    public function init() {
        $this->addComponent(new CounterPanel('counter'));
    }

    public function render() {
        $this->renderTemplate("example.php");
    }
}
```

If you think of the above classes as the "Controllers" in a typical MVC paradigm, the following templates are the "views".

First, the page template
```php
<?php
/* @var $cricket cricket\core\CricketContext */

use cricket\utils\Utils;
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Example</title>
        ... some non-essentials remove for brevity ...
        <?= $cricket->head() ?>
    </head>
    <body>
        <div style="max-width:500px;">
            <h1>Basic Cricket Example</h1>
            <p>
                This example shows a simple Cricket page with one component.  
                The box below is rendered by a stand alone component object which is 
                able to interact with the user and update itself independently of the page.
            </p>
        </div>
        <?php $cricket->component("counter") ?>
    </body>
</html>
```

Next, lets look at the CounterPanel template:
```php
<?php
/* @var $cricket cricket\core\CricketContext */
/* @var $count int */
?>

<div>
    Count = <?= $count ?>
</div>
<div style="margin-top:1em;">
    <input type="button" value = "-" onclick="<?= $cricket->onclick('decrement') ?>">
    &nbsp;
    <input type="button" value = "+" onclick="<?= $cricket->onclick('increment') ?>">
</div>
```
