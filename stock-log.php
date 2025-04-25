<?php

add_action('admin_head', 'sm_search_form');
function sm_search_form() {
	if ( isset($_GET['page']) && $_GET['page'] === 'stock-manager-log' ) {
		add_action('all_admin_notices', 'sm_search_box');
	}
}

function sm_search_box() {
	if ( ! isset($_GET['page']) || $_GET['page'] !== 'stock-manager-log' ) return;
?>
	<div style="padding-top: 20px;float: right; padding-right: 35px; ">
		<input type="text" id="product-search-box" placeholder="Search by Product Name" style="width: 250px; padding:auto;">
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function () {
  		const searchInput = document.getElementById('product-search-box');
  		const pagination = document.querySelector('.stock-manager-pagination');
  
  		const noMatchMsg = document.createElement('div');
  		noMatchMsg.id = 'no-match-message';
  		noMatchMsg.textContent = 'Product not found';
  		noMatchMsg.style.display = 'none';
  		noMatchMsg.style.color = 'red';
  		noMatchMsg.style.marginBottom = '10px';

  		if (pagination && pagination.parentNode) {
  			pagination.parentNode.insertBefore(noMatchMsg, pagination);
  		}
  
  		searchInput.addEventListener('keyup', function () {
    		const filter = this.value.toLowerCase();
    		const rows = document.querySelectorAll('table.table-bordered tbody tr');
  
  		  let visibleCount = 0;
  
    		rows.forEach(row => {
    			const titleCell = row.querySelector('td:nth-child(3)');
    				if (titleCell) {
    					const titleText = titleCell.textContent.toLowerCase();
    					if (titleText.includes(filter)) {
    					row.style.display = '';
    					visibleCount++;
    					} else {
    					row.style.display = 'none';
    					}
    				}
    		});
  
    		if (pagination) {
    			if (visibleCount >= 100) {
    				pagination.style.display = '';
    			} else {
    				pagination.style.display = 'none';
    			}
    		}
  
    		if (visibleCount === 0 && filter !== '') {
    			noMatchMsg.style.display = 'block';
    		} else {
    			noMatchMsg.style.display = 'none';
    		}
  		});
		});
	</script>
<?php
}

add_action('admin_head', 'hide_warning_message_stock_log_page');
function hide_warning_message_stock_log_page() {
  if ( isset($_GET['page']) && $_GET['page'] === 'stock-manager-log' ) {
      echo '<style>
          .notice-warning.notice.is-dismissible {
            display: none !important;
          }
          </style>';
  }
}
