<form action="<?=$controller->url_for('/show/save/');?>" method="post">
<input name="new_order" value="" type="hidden" />
<p> Hier können Sie die verwendeten Inhaltselemente
<ul>
<li>sortieren,</li>
<li>umbenennen und</li>
<li>die Sichtbarkeit für Teilnehmende ausschalten</li>
</ul>
</p>
<?	
$tab_num = 0; ?>

<ul id="sortable">

<? foreach($tabs as $tab){?>
 	<li name="<?=$tab_num?>" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
 	<? if(!in_array($tab['tab'], $ignore_visibility_tabs)){ ?>
		<input type="checkbox" name="visible_<?=$tab_num?>" <?=$tab['visible']?>/> 
	<? } else {
	strcmp($tab['visible'],'checked') == 0 ? $visible = 'on': $visible = 'off';
	?>
	<input type="hidden" name="visible_<?=$tab_num?>" value="<?=$visible?>"/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<? } ?> 
 	<input type="hidden" value="<?= $tab['tab']; ?>" name="tab_title_<?=$tab_num?>" />
	<input value="<?= $tab['title']; ?>" name="new_tab_title_<?=$tab_num?>" size="20"/>
	<input type="hidden" value="<?= $tab['position']; ?>" name="tab_position_<?=$tab_num?>" />
 	(<?= $tab['orig_title']; ?>)</p>
	</li>
 	<?$tab_num++;
	
}?>

</ul>
<br/>
<p>Um die Ansicht für Ihre Teilnehmenden zu überprüfen, klicken Sie <a class="default" href="<?= URLHelper::getLink('dispatch.php/course/change_view/set_changed_view') ?>">hier</a></p>

<input type="hidden" name="tab_num" value="<?=$tab_num?>" />
<p><button title="Änderungen übernehmen" name="submit" class="button" type="submit">Übernehmen</button></p>
</form>


