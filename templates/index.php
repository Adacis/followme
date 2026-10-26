<?php
style('followme', 'style');
script('followme', 'script');
?>
<div id="app" class="followme-app">
    <div id="app-navigation">
        <?php print_unescaped($this->inc('navigation/index')); ?>
        <?php print_unescaped($this->inc('settings/index')); ?>
    </div>
    <div id="app-content"><main id="app-content-wrapper">
        <?php print_unescaped($this->inc('content/index')); ?>
    </main></div>
</div>
<?php print_unescaped($this->inc('content/edit-modal')); ?>
