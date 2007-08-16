<?php
class SubsiteAdmin extends GenericDataAdmin {
	
	static $tree_class = "Subsite";
	static $subitem_class = "Subsite";
	static $data_type = 'Subsite';
	
	function performSearch() {
		
	}
	
	function getSearchFields() {
		return singleton('Subsite')->adminSearchFields();
	}
	
	function getLink() {
		return 'admin/intranets/';
	}
	
	function Link() {
		return $this->getLink();
	}
	
	function Results() {
		$where = '';
		
		if(isset($_REQUEST['action_getResults']) && $_REQUEST['action_getResults']) {
			$SQL_name = Convert::raw2sql($_REQUEST['Name']);
			$where = "`Title` LIKE '%$SQL_name%'";
		}
		
		$intranets = DataObject::get('Intranet', $where);
		if(!$intranets)
			return null;
			
		$html = "<table class=\"ResultTable\"><thead><tr><th>Name</th><th>Domain</th></tr></thead><tbody>";
		
		$numIntranets = 0;
		foreach($intranets as $intranet) {
			$numIntranets++;
			$evenOdd = ($numIntranets % 2) ? 'odd':'even';
			$html .= "<tr class=\"$evenOdd\"><td><a href=\"admin/intranets/show/{$intranet->ID}\">{$intranet->Title}</a></td><td>{$intranet->Subdomain}.{$intranet->Domain}</td></tr>";
		}
		$html .= "</tbody></table>";
		return $html;
	}
	
	function AddSubsiteForm() {
		$templates = $this->getIntranetTemplates();
	
		if($templates) {
			$templateArray = $templates->map('ID', 'Domain');
		}
		
		return new Form($this, 'AddIntranetForm', new FieldSet(
			new TextField('Name', 'Name:'),
			new TextField('Subdomain', 'Subdomain:'),
			new DropdownField('TemplateID', 'Use template:', $templateArray),
			new TextField('AdminName', 'Admin name:'),
			new EmailField('AdminEmail', 'Admin email:')
		),
		new FieldSet(
			new FormAction('addintranet', 'Add')
		));
	}
	
	public function getIntranetTemplates() {
		return DataObject::get('Subsite_Template', '', 'Domain DESC');
	}
	
	function addintranet($data, $form) {
		
		$SQL_email = Convert::raw2sql($data['AdminEmail']);
		$member = DataObject::get_one('Member', "`Email`='$SQL_email'");
		
		if(!$member) {
			$member = Object::create('Member');
			$nameParts = explode(' ', $data['AdminName']);
			$member->FirstName = array_shift($nameParts);
			$member->Surname = join(' ', $nameParts);
			$member->Email = $data['AdminEmail'];
			$member->write();
		}
		
		// Create intranet from existing template
		// TODO Change template based on the domain selected.
		$intranet = Intranet::createFromTemplate($data['Name'], $data['Subdomain'], $data['TemplateID']);
		
		$groupObjects = array();
		
		// create Staff, Management and Administrator groups
		$groups = array(
			'Administrators' => array('CL_ADMIN', 'CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin', 'CMS_ACCESS_SecurityAdmin', 'CMS_ACCESS_IntranetAdmin'),
			'Management' => array('CL_MGMT'),
			'Staff' => array('CL_STAFF')
		);
		foreach($groups as $name => $perms) {
			$group = new Group();
			$group->SubsiteID = $intranet->ID;
			$group->Title = $name;
			$group->write();
			
			foreach($perms as $perm) {
				Permission::grant($group->ID, $perm);
			}
			
			$groupObjects[$name] = $group;
		}
		
		$member->Groups()->add($groupObjects['Administrators']);
		
		Director::redirect('admin/intranets/show/' . $intranet->ID);
	}
}
?>
