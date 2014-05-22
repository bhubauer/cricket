cricket
=======

Cricket is a newly open sourced, yet very rich and mature PHP web application framework.

Cricket applications are built with Page and Component classes that are analogous to a desktop application's Window and View hierarchy. 


```php
use cricket\core\Page;

class PageExample extends Page {
    public function render() {
        $this->renderTemplate("example.php",array(
            'data1' => 'value1',
            'data2' => 'value2',
        ));
    }
}
```
