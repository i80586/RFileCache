<h2>RFileCache</h2>
<p>Simple PHP class for caching data in files</p>

<h3>Usage</h3>

<p>
<h4>Simple example</h4>

$cache=new \RLibrary\RFileCache(__dir__ . '/cache');

<br/>
<br/>
<i>// Save cache in file about one hour</i>
<br/>

$cache->set('country', array('city' => 'Baku', 'country' => 'Azerbaijan'), 3600);

<br/>
<br/>
<i>// Get cache value</i>
<br/>

$country=$cache->get('country');

<br/>
<br/>
<i>// Delete cache</i>
<br/>

$cache->drop('country');

<br/>
</p>