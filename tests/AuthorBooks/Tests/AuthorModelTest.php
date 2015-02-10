<?php
use LazyRecord\Testing\ModelTestCase;
use AuthorBooks\Model\Author;
use AuthorBooks\Model\AuthorCollection;

class AuthorModelTest extends ModelTestCase
{
    public $driver = 'sqlite';

    public function getModels()
    {
        return array('AuthorBooks\Model\AuthorSchema');
    }

    /**
     * @rebuild false
     */
    public function testCollection()
    {
        $author = new Author;
        $this->assertResultSuccess($author->create(array( 
            'name' => 'FooBar',
            'email' => 'a@a',
            'identity' => 'a',
            'confirmed' => false,
        )));
        $collection = $author->asCollection();
        ok($collection);
        $this->assertResultSuccess($author->delete());
    }


    /**
     * @rebuild false
     */
    public function testSchemaInterface()
    {
        $author = new Author;

        $names = array('updated_on','created_on','id','name','email','identity','confirmed');
        foreach( $author->getColumnNames() as $n ) {
            ok( in_array( $n , $names ));
            ok( $author->getColumn( $n ) );
        }

        $columns = $author->getColumns();
        count_ok( 7 , $columns );

        $columns = $author->getColumns(true); // with virtual column 'v'
        count_ok( 8 , $columns );
        ok( 'authors' , $author->getTable() );
        ok( 'Author' , $author->getLabel() );
        $this->assertInstanceOf('AuthorBooks\Model\AuthorCollection', $author->newCollection() );
    }

    public function testBooleanCondition() 
    {
        $a = new Author;
        $ret = $a->create(array(
            'name' => 'a',
            'email' => 'a@a',
            'identity' => 'a',
            'confirmed' => false,
        ));
        $this->resultOK(true,$ret);

        $ret = $a->create(array(
            'name' => 'b',
            'email' => 'b@b',
            'identity' => 'b',
            'confirmed' => true,
        ));
        $this->resultOK(true,$ret);

        $authors = new AuthorCollection;
        $authors->where()
                ->equal( 'confirmed', false);
        $ret = $authors->fetch();
        ok($ret);
        is(1,$authors->size());

        $authors = new AuthorCollection;
        $authors->where()
                ->equal( 'confirmed', true);
        $ret = $authors->fetch();
        ok($ret);
        is(1,$authors->size());

        $authors->delete();
    }

    public function testVirtualColumn() 
    {
        $author = new Author;
        $ret = $author->create(array( 
            'name' => 'Pedro' , 
            'email' => 'pedro@gmail.com' , 
            'identity' => 'id',
        ));
        ok($ret->success);

        ok( $v = $author->getColumn('v') ); // virtual colun
        ok( $v->virtual );

        $columns = $author->getSchema()->getColumns();
        ok( ! isset($columns['v']) );

        is('pedro@gmail.compedro@gmail.com',$author->get('v'));

        ok( $display = $author->display( 'v' ) );

        $authors = new AuthorCollection;
        ok($authors);
    }


    /**
     * Basic CRUD Test 
     */
    public function testModel()
    {
        $author = new Author;

        $a2 = new Author;
        $ret = $a2->find( array( 'name' => 'A record does not exist.' ) );
        $this->assertResultFail($ret);
        ok(! $a2->id);

        $ret = $a2->create(array( 'name' => 'long string \'` long string' , 'email' => 'email' , 'identity' => 'id' ));
        $this->assertResultSuccess($ret);
        ok($a2->id);

        $ret = $a2->create(array( 'xxx' => true, 'name' => 'long string \'` long string' , 'email' => 'email2' , 'identity' => 'id2' ));
        $this->assertResultSuccess($ret);
        ok( $a2->id );

        $ret = $author->create(array());
        $this->assertResultFail($ret);
        ok($ret->message);
        like('/Empty arguments/' , $ret->message );

        $ret = $author->create(array( 'name' => 'Foo' , 'email' => 'foo@google.com' , 'identity' => 'foo' ));
        ok( $ret );
        ok( $id = $ret->id );
        ok( $ret->success );
        is( 'Foo', $author->name );
        is( 'foo@google.com', $author->email );

        $ret = $author->load( $id );
        ok( $ret->success );
        is( $id , $author->id );
        is( 'Foo', $author->name );
        is( 'foo@google.com', $author->email );
        is( false , $author->confirmed );

        $ret = $author->find(array( 'name' => 'Foo' ));
        ok( $ret->success );
        is( $id , $author->id );
        is( 'Foo', $author->name );
        is( 'foo@google.com', $author->email );
        is( false , $author->confirmed );

        $ret = $author->update(array( 'name' => 'Bar' ));
        ok( $ret->success );

        is( 'Bar', $author->name );

        $ret = $author->delete();
        ok( $ret->success );

        $data = $author->toArray();
        ok( empty($data), 'should be empty');
    }

    public function testMixinMethods() 
    {
        $author = new Author;
        $ret = $author->create(array( 
            'name' => 'testMixinMethods',
            'email' => 'zz3@zz3',
            'identity' => 'zz3',
        ));
        result_ok($ret);
        $age = $author->getAge();
        ok($age, "Got Age");
        ok($age->format('%s seconds'));
    }


    public function testRelationshipWithPredefinedConditions()
    {
        $author = new Author;
        $ret = $author->create(array( 
            'name' => 'Address Testing',
            'email' => 'tom@address',
            'identity' => 'tom-has-two-addresses',
        ));
        $author->addresses[] = array( 'address' => 'Using address', 'unused' => false );
        $author->addresses[] = array( 'address' => 'Unused address', 'unused' => true );

        $addresses = $author->addresses;
        $this->assertCollectionSize(2, $addresses);

        $unusedAddresses = $author->unused_addresses;
        $this->assertCollectionSize(1, $unusedAddresses);

        $this->assertInstanceOf('LazyRecord\BaseModel', $unusedAddresses[0]);
        $this->assertTrue($unusedAddresses[0]->unused);
    }



    public function testUpdateNull()
    {
        $author = new Author;
        $ret = $author->create(array( 
            'name' => 'Mary III',
            'email' => 'zz3@zz3',
            'identity' => 'zz3',
        ));
        $this->assertResultSuccess($ret);

        $id = $author->id;

        $this->assertResultSuccess( $author->update(array( 'name' => 'I' )) );
        is($id , $author->id );
        is('I', $author->name );

        $this->assertResultSuccess($author->update(array( 'name' => null )) );
        is( $id , $author->id );
        is( null, $author->name );

        $this->assertResultSuccess($author->load( $author->id ));
        is($id , $author->id );
        $this->assertNull($author->name);
    }
}