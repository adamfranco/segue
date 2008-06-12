------------------------
 About Templates
------------------------

Segue templates are 'starting points for sites'. Templates are simply exported
sites with an additional info.xml file and an optional thumbnail.png image.

Segue has two template directories:
	segue/templates-dist/
	segue/templates-local/

Templates that ship with Segue live in templates-dist/ and templates created by you should
live in the templates-local/ directory. If you create a template in the templates-local/ 
directory of the same name as one in the templates-dist/ directory, the templates-local/
version will be used instead.

The ID of a template is its directory name, e.g. segue/templates-dist/Basic has an ID Basic.

------------------------
 Configuration
------------------------

By default, templates are ordered alphabetically by ID. A custom ordering can be set in segue/config/templates.conf.php:

	$templateMgr->setOrder(array(
		"StandardCourseSite",
		"Basic",
		"Blog"
	));

Any templates not listed in the order array will be displayed alphabetically after the ordered templates.


------------------------
 Creating Templates
------------------------

While the template XML can be edited by hand, the easiest way to create a template is to start from an existing site.

   1. Create a site that you will use to build your template.

      Enter #SITE_NAME# and #SITE_DESCRIPTION# for the display-name and description of the site to pre-populate the template-site with appropriate placeholders.
      
   2. Configure the site to your liking. Any place you use #SITE_NAME# or #SITE_DESCRIPTION# in the text, the placeholder will be replaced with the site name and description that users enter when the create a new site using this template.
   
   3. Export the site
   
   4. Un-Tar the exported archive.
   
   5. Create a new directory (to go in segue/themes-local/) named for the template
   
   6. Place site.xml and media/ directory (if it exists) into the new template directory
   
   7. Add an info.xml file to the new template directory with information about your template.

      <TemplateInfo>
          <DisplayName lang="en_US">Basic</DisplayName>
          <Description lang="en_US">
              This template is a basic starting point with two pages already created.
          </Description>
      </TemplateInfo>

   8. You can optionally add additional translations of the display name and description.

      <TemplateInfo>
      	<DisplayName lang='en_US'>Spanish School Template</DisplayName>
      	<Description lang='en_US'>
            This template is laid out for classes taught by the Spanish School and
            Spanish department.
      	</Description>
      	<DisplayName lang='es_ES'>Plantilla de la Escuela Espa–ola</DisplayName>
      	<Description lang='es_ES'>
            Esta plantilla se presenta para las clases ense–adas por la escuela 
            espa–ola y el departamento espa–ol.
      	</Description>
      </TemplateInfo>



   8. You can optionally add Agent or Group restrictions to the info.xml to make the template only available to certain users.

      <TemplateInfo>
          <DisplayName lang="en_US">Spanish School Template</DisplayName>
          <Description lang="en_US">
              This template is laid out for classes taught by the Spanish School and
              Spanish department.
          </Description>
          <DisplayName lang="es_ES">Plantilla de la Escuela Espa–ola</DisplayName>
          <Description lang="es_ES">
               Esta plantilla se presenta para las clases ense–adas por la escuela 
               espa–ola y el departamento espa–ol.
          </Description>
          <Authorized>
      		<Group id="123456">Spanish School</Group>
      		<Agent id="789102">Spanish School Coordinater</Agent>
      		<Group id="CN=Spanish Dept,OU=Groups,dc=example,dc=edu">Spanish Department Faculty</Group>
          </Authorized>
      </TemplateInfo>

      The ids of the Groups and Agents should match those in your Agent system, but the name is just an aid to provide information in a template listing.
      
   9. If you haven't already, put the new template directory in segue/themes-local/

With these steps done, the template should show up in the list of templates when users go into the 'create site' wizard.
