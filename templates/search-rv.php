<?php
/*
Template Name: Search RV
*/
get_header();


// $taxonomies = array('site_type', 'park_feature', 'site_amenity'); // Add your custom taxonomies here

// foreach ($taxonomies as $taxonomy) {
//     $terms = get_terms(array(
//         'taxonomy'   => $taxonomy,
//         'hide_empty' => false, // Show even if no posts are assigned
//     ));

//     if (!empty($terms) && !is_wp_error($terms)) {
//         echo '<h3>' . esc_html(get_taxonomy($taxonomy)->label) . '</h3>';
//         echo '<ul>';
//         foreach ($terms as $term) {
//             echo '<li><a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a> (' . esc_html($term->count) . ')</li>';
//         }
//         echo '</ul>';
//     }
// }

?>
<style>
 




  .content {
    background: rgb(200, 200, 200);
    height: 300px;
    padding: 20px;
    margin-bottom: 20px;
  }
</style>



<div class="bg-light rounded mb-3 p-3">
  <h6 class="mb-3">Filter by Site Type</h6>
  <ul class="list-unstyled">
    <li><button class="btn btn-link filter-btn" data-filter="all">All Sites</button></li>
    <?php
    $site_types = get_terms(array(
        'taxonomy' => 'site_type',
        'hide_empty' => false,
    ));
    foreach ($site_types as $type) {
        echo '<li><button class="btn btn-link filter-btn" data-filter="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</button></li>';
    }
    ?>
  </ul>
</div>
<!-- script for filtering  -->
<script>
jQuery(document).ready(function($) {
    function filterRVLots() {
        var siteType = $('.filter-btn.active').data('filter') || 'all'; // Get active site type
        var selectedFeatures = [];

        $('.feature-filter:checked').each(function() {
            selectedFeatures.push($(this).val());
        });

        // select by class list aminets 
        var selectedAminets = [];

        $('.aminets-filter:checked').each(function (){
          selectedAminets.push($(this).val());
        });


        

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: {
                action: 'filter_rv_lots',
                site_type: siteType,
                features: selectedFeatures,
                aminets:selectedAminets,
            }, 
            beforeSend: function() {
                $('#show_aval_room').html('<p class="text-center">Loading...</p>');
            },
            success: function(response) {
                $('#show_aval_room').html(response);
            }
        });
    }

    // Handle Site Type Filter Click
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filterRVLots();
    });

    // Handle Park Feature Checkbox Change
    $('.feature-filter').on('change', function() {
        filterRVLots();
    });
    $('.aminets-filter').on('change',function (ev) {
      filterRVLots();
    })
});
</script>






<!-- add feature for the custom checkbox and filter the post  -->

<div class="bg-light rounded mb-3 p-3">
  <h6 class="mb-3">Filter by Park Features</h6>
  <ul class="list-unstyled">
    <?php
    $features = get_terms(array(
        'taxonomy'   => 'park_feature',
        'hide_empty' => false,
    ));
    foreach ($features as $feature) {
        echo '<li>
            <input type="checkbox" class="feature-filter" value="' . esc_attr($feature->slug) . '" id="feature-' . esc_attr($feature->slug) . '">
            <label for="feature-' . esc_attr($feature->slug) . '">' . esc_html($feature->name) . '</label>
        </li>';
    }
    ?>
  </ul>
</div>

<!-- filter the aminets  -->


<div class="bg-light rounded mb-5 p-4">
  <h6 class="mb-3">Filter by aminets</h6>
  <ul class="list-unstyled">
    <?php
$aminetes = get_terms([
  
  'taxonomy' => 'site_amenity',
  'hide_empty' => false,
]);

    
foreach ($aminetes as $amenity) {
  echo  '<li>

  <input type="checkbox" class="aminets-filter" value="' .esc_attr($amenity->slug) .'"  id="amenity-' . esc_attr($amenity->slug) . '">

  <label for= "amenity-' . esc_attr($amenity->slug) . '"> ' . esc_html($amenity->name) . '</label>
          </li>';
}
    
    ?>
  </ul>
</div>



<div class="bg-light rvbs-search-rv">


 

 <!-- Our Rooms Section -->
 <div class="py-5 px-4 bg-light">
    <h2 class="mt-4 mb-1 pt-4 text-center font-bold merinda">OUR ROOMS</h2>
    <div class="h-line bg-dark mb-5"></div>
  </div>




  <div class="container">
    <div class="row">
      <!-- Sidebar Filters -->
     <!-- Sidebar Filters -->
<div class="col-lg-3 filster_section" id="filter_section">
  <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
    <div class="container d-flex flex-lg-column align-items-stretch">
      <h6 class="mt-2">Filters</h6>
      <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" 
        data-bs-target="#rooms_filter" aria-controls="rooms_filter" 
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse flex-lg-column mt-2 align-items-stretch" id="rooms_filter">
        <!-- Date Filters -->
        <div class="bg-light rounded mb-3 p-3">
          <h6 class="mb-3 d-flex justify-content-between">Dates
            <span id="reset_avail" onclick="clear_date()" class="text-secondary btn btn-sm">Reset</span>
          </h6>
          <div>
            <label for="checkin_date" class="form-label">Check In Date</label>
            <input type="date" onchange="check_room_aval()" name="checkin_date" class="form-control shadow-none" id="checkin_date" />
          </div>
          <div>
            <label for="checkout_date" class="form-label">Check Out Date</label>
            <input type="date" class="form-control shadow-none" id="checkout_date" name="checkout_date" onchange="check_room_aval()" />
          </div>
        </div>

        <!-- Guest Filters -->
        <div class="bg-light rounded mb-3 p-3">
          <h6 class="mb-3 d-flex justify-content-between">GUEST
            <span id="guest_reset" onclick="clear_guest()" class="text-secondary btn btn-sm">Reset</span>
          </h6>
          <div class="mb-2 d-flex">
            <div class="guest me-3">
              <label for="adult" class="form-label">Adults</label>
              <input min="1" type="number" oninput="guest_filter()" class="form-control shadow-none" id="adult" />
            </div>
            <div class="guest">
              <label for="children" class="form-label">Children</label>
              <input min="0" type="number" oninput="guest_filter()" class="form-control shadow-none" id="children" />
            </div>
          </div>
        </div>
        <!-- Guest Filters -->
        <div class="bg-light rounded mb-3 p-3">
          <h6 class="mb-3 d-flex justify-content-between">GUEST
            <span id="guest_reset" onclick="clear_guest()" class="text-secondary btn btn-sm">Reset</span>
          </h6>
          <div class="mb-2 d-flex">
            <div class="guest me-3">
              <label for="adult" class="form-label">Adults</label>
              <input min="1" type="number" oninput="guest_filter()" class="form-control shadow-none" id="adult" />
            </div>
            <div class="guest">
              <label for="children" class="form-label">Children</label>
              <input min="0" type="number" oninput="guest_filter()" class="form-control shadow-none" id="children" />
            </div>
          </div>
        </div>
        <!-- Guest Filters -->
        <div class="bg-light rounded mb-3 p-3">
          <h6 class="mb-3 d-flex justify-content-between">GUEST
            <span id="guest_reset" onclick="clear_guest()" class="text-secondary btn btn-sm">Reset</span>
          </h6>
          <div class="mb-2 d-flex">
            <div class="guest me-3">
              <label for="adult" class="form-label">Adults</label>
              <input min="1" type="number" oninput="guest_filter()" class="form-control shadow-none" id="adult" />
            </div>
            <div class="guest">
              <label for="children" class="form-label">Children</label>
              <input min="0" type="number" oninput="guest_filter()" class="form-control shadow-none" id="children" />
            </div>
          </div>
        </div>
        <!-- Guest Filters -->
        <div class="bg-light rounded mb-3 p-3">
          <h6 class="mb-3 d-flex justify-content-between">GUEST
            <span id="guest_reset" onclick="clear_guest()" class="text-secondary btn btn-sm">Reset</span>
          </h6>
          <div class="mb-2 d-flex">
            <div class="guest me-3">
              <label for="adult" class="form-label">Adults</label>
              <input min="1" type="number" oninput="guest_filter()" class="form-control shadow-none" id="adult" />
            </div>
            <div class="guest">
              <label for="children" class="form-label">Children</label>
              <input min="0" type="number" oninput="guest_filter()" class="form-control shadow-none" id="children" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</div>




      <!-- Room Cards -->
      <div class="col-lg-9 col-md-12" id="room_data">
        <div id="show_aval_room_container">

          <div id="show_aval_room">
            <?php
            // Fetch initial 15 posts
            $query = new WP_Query(array(
              'post_type'      => 'rv-lots',

              'posts_per_page' => 15,
              'paged'          => 1
            ));

            if ($query->have_posts()) :
              while ($query->have_posts()) : $query->the_post();
            ?>
                <div class="card mb-4">
                  <div class="row">
                    <!-- First Section (Image) -->
                    <div class="col-md-4">
                      <?php if (has_post_thumbnail()) : ?>
                        <img src="<?php the_post_thumbnail_url(); ?>" class="img-fluid" alt="<?php the_title(); ?>">
                      <?php else : ?>
                        <div class="no-image">No Image Available</div>
                      <?php endif; ?>
                    </div>
                    <!-- Second Section (Details) -->
                    <div class="col-md-5">
                      <h5 class="card-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                      </h5>
                      <p class="card-text"><?php the_excerpt();
                      
                      
                  

                      
                      ?>
                      
                      <?php 
                        $features = get_the_terms(get_the_ID(), 'park_feature');
                        if ($features && !is_wp_error($features)) :
                            echo '<p><strong>Features:</strong> ';
                            $feature_names = wp_list_pluck($features, 'name');
                            echo implode(', ', $feature_names);
                            echo '</p>';
                        endif;
                        ?></p>

                        <p>
                        <?php 
                        $amenities = get_the_terms(get_the_ID(), 'site_amenity');
                        if ($amenities && !is_wp_error($amenities)) :
                            echo '<p><strong>Amenities:</strong> ';
                            $amenity_names = wp_list_pluck($amenities, 'name');
                            echo implode(', ', $amenity_names);
                            echo '</p>';
                        endif;
                        ?>
                        </p>
                      <a href="<?php echo home_url('/book-now?post_id=' . get_the_ID() . '&date=' . get_the_date('Y-m-d')); ?>" 
   class="btn btn-primary" target="_blank" rel="noopener noreferrer">
   Book Now
</a>

                    </div>
                    <!-- Third Section (Price) -->
                    <div class="col-md-3">
                      <p class="price">Starting at <strong>$20.00</strong> /night</p>
                    </div>
                  </div>
                </div>
            <?php
              endwhile;
              wp_reset_postdata();
            endif;
            ?>
          </div>

          <!-- Load More Button -->
          <div class="text-center">
            <button id="load_more" class="btn btn-secondary" data-page="2">Load More</button>
          </div>


        </div>
      </div>
    </div>
  </div>

<?php 





get_footer();
?>

