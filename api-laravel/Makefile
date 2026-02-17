reset-data:
	php artisan scout:delete-index images_index
	php artisan scout:delete-index tags_index
	php artisan scout:open-search-setup-templates
	php artisan migrate:fresh --seed

