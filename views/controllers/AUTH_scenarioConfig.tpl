<h3>Registration Scenarios</h3>

<p>The various "hooks" for the scenario pages are stored in the Settings table</p>
<p>Currently, the nodes are:</p>
<ul>
    <li>REGISTRATION_ROUTE (key is <tt>{$registration_route_key}</tt>): <strong><a href="{$registration_route_node}">{$registration_route_node}</a></strong>.<br />
        This node is displayed when a new user registration form should be offered to the user (e.g. <tt>/register</tt>)</li>
    <li>VALIDATION_ROUTE (key is <tt>{$validation_route_key}</tt>): <strong>{$validation_route_node}</strong>.<br />
        This node is called when a token should be verified (e.g. /validate/&lt;key&gt;).
    </li>
</ul>