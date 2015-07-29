<form action="<?=$controller->url_for('/show/save/');?>" method="post">
<input name="new_order" value="" type="hidden" />
<p> Hier können Sie die verwendeten Inhaltselemente
<ul>
<li>sortieren und</li>
<li>umbenennen</li>
</ul>
</p>
<?	
$tab_num = 0; ?>

<ul id="sortable">

<? foreach($tabs as $tab){?>
 	<li name="<?=$tab_num?>" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
 	<!-- <input type="checkbox" name="visible_<?=$tab_num?>" <?=$tab['visible']?>/> -->
 	<input type="hidden" value="<?= $tab['tab']; ?>" name="tab_title_<?=$tab_num?>" />
	<input value="<?= $tab['title']; ?>" name="new_tab_title_<?=$tab_num?>" size="20"/>
	<input type="hidden" value="<?= $tab['position']; ?>" name="tab_position_<?=$tab_num?>" />
 	(<?= $tab['orig_title']; ?>)</p>
	</li>
 	<?$tab_num++;
	
}?>

</ul>

<input type="hidden" name="tab_num" value="<?=$tab_num?>" />
<p><button title="Änderungen übernehmen" name="submit" class="button" type="submit">Übernehmen</button></p>
</form>


