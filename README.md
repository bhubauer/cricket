cricket
=======

Cricket is a newly open sourced, yet very rich and mature PHP web application framework.

Cricket applications are built with Page and Component classes that are analogous to a desktop application's Window and View hierarchy. 

Consider the following example of a simple page that contains a single component.  This component displays a current "count" number and offers methods to increment or decrement the number.

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
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Example</title>
        <?= $cricket->head() ?>
    </head>
    <body>
        <?php $cricket->component("counter") ?>
    </body>
</html>
```


