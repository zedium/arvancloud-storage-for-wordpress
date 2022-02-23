=== ArvanCloud Object Storage ===
Contributors: arvancloud, khorshidlab
Tags: storage, s3, offload, backup, files, arvancloud
Requires at least: 4.0
Tested up to: 5.9
Requires PHP: 7.1
Stable tag: 0.8
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

ArvanCloud Storage for offload, backup and upload your WordPress files and databases directly to your ArvanCloud object storage bucket.


== Description ==
Using ArvanCloud Storage Plugin you can offload, backup and upload your WordPress files and databases directly to your ArvanCloud object storage bucket. This easy-to-use plugin allows you to back up, restore and store your files simply and securely to a cost-effective, unlimited cloud storage. No need for expensive hosting services anymore.


== Installation ==
= Using The WordPress Dashboard =

1. Navigate to the \'Add New\' in the plugins dashboard
2. Search for \'ArvanCloud Object Storage\'
3. Click \'Install Now\'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the \'Add New\' in the plugins dashboard
2. Navigate to the \'Upload\' area
3. Select `arvancloud-object-storage.zip` from your computer
4. Click \'Install Now\'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `arvancloud-object-storage.zip`
2. Extract the `arvancloud-object-storage` directory to your computer
3. Upload the `arvancloud-object-storage` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Screenshots ==
1. Configure cloud storage in wp-config.php 
2. Configure cloud storage in database
3. Buckets list
4. General Settings
5. Bulk copy to bucket feature in Media Library (list view)
6. Copy to bucket link in Media Library (list view)

== Changelog ==
= 0.8 - 2022-02-23 =
* Add System info feature


= 0.7 - 2022-02-16 =
* perf: Better validation config methods
* docs: Update pot and fa translation
* refactor: Checking keep-local-files is set or not


= 0.6 - 2022-02-11 =
* Tested up to 5.9
* Update assets

= 0.5 - 2022-01-11 =
* Fix keep local files option issue

= 0.4 - 2022-01-03 =
* Fix setting slug bug in Persian translation
* Fix rendering copy to bucket metabox in attachment 
* Minor options improvement

= 0.3 - 2021-11-27 =
* Minor changes

= 0.2 - 2021-11-25 =
* Update README

= 0.1 - 2021-11-25 =
* Official Plugin Release