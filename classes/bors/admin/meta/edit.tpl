{block name="admin_meta_edit_body_begin"}{/block}
{form
	class=$this->main_admin_class()
	id=$this->id()
	view=$view
	object=$target
	fields=$form_fields
	calling_object=$this
	form_template_class=$this->get('form_template_class')
}

{if $this->id()}
	{go value=$this->go_edit_url()}
{else}
	{go value=$this->go_new_url()}
{/if}

{block name="admin_meta_edit_form_append"}{/block}

{block name="admin_meta_edit_form_buttons"}
{submit th=$this->submit_button_title()}
{/block}

{/form}

{block name="admin_meta_edit_body_end"}{/block}

{if $this->id()
	&& $target->access()
	&& $target->access()->get('can_delete')
	&& !$this->get('__skip_delete')
}
{* module class="bors_admin_module_link" object=$target types='-ipotekbank_file' linkable='ipotekbank_linkable' *}
{* include file="xfile:bors/admin/files.html" object=$target upload_files_count="1" file_class='ipotekbank_file' *}
{* module class="bors_admin_module_images" object=$target upload_images_count=1 skip_limits=true skip_image_type=true *}
<br />
<div align="center">
[ <b>{$target->admin_delete_link()}</b> ]
</div>
{/if}
