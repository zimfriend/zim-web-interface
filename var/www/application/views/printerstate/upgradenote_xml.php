<div data-role="collapsible">
	<h2>{version}</h2>
	<ul data-role="listview"><!-- data-inset="true" -->
		{part_ele}
			<li data-role="list-divider"><span style="font-size: larger;">{part_title}</li>
			{note_ele}
				<li><p style="white-space: normal; margin: 0; font-size: medium;">{note_line}</p></li>
			{/note_ele}
		{/part_ele}
	</ul>
</div>