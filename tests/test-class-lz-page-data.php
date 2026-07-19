<?php
/**
 * Tests for LZ_Page_Data.
 */

class LZ_Page_Data_Test extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		$this->post_id = self::factory()->post->create();
	}

	public function tear_down() {
		wp_delete_post( $this->post_id, true );
		\LzBuilder\LZ_Page_Data::delete_layout_data( $this->post_id );
		parent::tear_down();
	}

	public function test_has_builder_data_empty() {
		$this->assertFalse( \LzBuilder\LZ_Page_Data::has_builder_data( $this->post_id ) );
	}

	public function test_add_row_returns_node_id() {
		$row_id = \LzBuilder\LZ_Page_Data::add_row( $this->post_id, '1-col', 0, 'draft' );
		$this->assertNotEmpty( $row_id );
		$this->assertStringStartsWith( 'lz_node_', $row_id );
	}

	public function test_add_module_without_parent_auto_creates_row() {
		$node_id = \LzBuilder\LZ_Page_Data::add_module( $this->post_id, 'heading', '', 0, 'draft' );
		$this->assertNotEmpty( $node_id );

		$data = \LzBuilder\LZ_Page_Data::get_layout_data( $this->post_id, 'draft' );
		$types = array_column( $data, 'type' );
		$this->assertContains( 'row', $types );
		$this->assertContains( 'column', $types );
	}

	public function test_find_last_column_returns_column_id() {
		\LzBuilder\LZ_Page_Data::add_row( $this->post_id, '2-cols', 0, 'draft' );
		$last = \LzBuilder\LZ_Page_Data::find_last_column( $this->post_id, 'draft' );
		$this->assertNotEmpty( $last );
		$this->assertStringStartsWith( 'lz_node_', $last );
	}

	public function test_delete_node_removes_module() {
		$node_id = \LzBuilder\LZ_Page_Data::add_module( $this->post_id, 'heading', '', 0, 'draft' );
		$this->assertNotEmpty( $node_id );

		\LzBuilder\LZ_Page_Data::delete_node( $node_id, $this->post_id, 'draft' );

		$node = \LzBuilder\LZ_Page_Data::get_node( $node_id, $this->post_id, 'draft' );
		$this->assertNull( $node );
	}

	public function test_duplicate_node_creates_new_id() {
		$node_id  = \LzBuilder\LZ_Page_Data::add_module( $this->post_id, 'heading', '', 0, 'draft' );
		$clone_id = \LzBuilder\LZ_Page_Data::duplicate_node( $node_id, $this->post_id, 'draft' );
		$this->assertNotEmpty( $clone_id );
		$this->assertNotEquals( $node_id, $clone_id );
	}

	public function test_draft_fallback_to_published() {
		\LzBuilder\LZ_Page_Data::update_layout_data( $this->post_id, [
			[ 'node_id' => 'test_node_1', 'type' => 'row', 'parent_id' => null, 'position' => 0, 'module' => '', 'settings' => [] ],
		], 'published' );

		$draft = \LzBuilder\LZ_Page_Data::get_layout_data( $this->post_id, 'draft' );
		$this->assertNotEmpty( $draft );
		$this->assertEquals( 'test_node_1', $draft[0]['node_id'] );
	}

	public function test_published_does_not_fallback() {
		$pub = \LzBuilder\LZ_Page_Data::get_layout_data( $this->post_id, 'published' );
		$this->assertIsArray( $pub );
		$this->assertEmpty( $pub );
	}

	public function test_generate_node_id_is_unique() {
		$a = \LzBuilder\LZ_Page_Data::generate_node_id();
		$b = \LzBuilder\LZ_Page_Data::generate_node_id();
		$this->assertNotEquals( $a, $b );
	}
}
