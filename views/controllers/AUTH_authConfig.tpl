<h3>Authentication classes</h3>

<p>The authentication methods are identified by class names. The default methods is in <tt>$configuration['auth']['default']</tt></p>
<p>Currently, it's {if ($defaultAuthClass)}<tt>{$defaultAuthClass}</tt>{else}not set, so the default (local database) mechanism is used{/if}.</p>