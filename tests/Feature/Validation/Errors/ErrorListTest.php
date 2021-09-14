<?php

namespace CraigPaul\Moneris\Tests\Feature\Validation\Errors;

use CraigPaul\Moneris\Tests\Support\Stubs\ErrorStub;
use CraigPaul\Moneris\Tests\TestCase;
use CraigPaul\Moneris\Validation\Errors\ErrorList;

/**
 * @covers \CraigPaul\Moneris\Validation\Errors\ErrorList
 */
class ErrorListTest extends TestCase
{
    /** @test */
    public function pushing_errors_onto_the_stack (): void
    {
        $list = new ErrorList();

        $this->assertFalse($list->has());

        $list->push(new ErrorStub(), new ErrorStub());

        $this->assertTrue($list->has());
        $this->assertSame(2, $list->count());
    }

    /** @test */
    public function getting_an_error (): void
    {
        $errors = [
            new ErrorStub(),
            new ErrorStub(),
            new ErrorStub(),
        ];

        $list = new ErrorList(...$errors);

        $this->assertSame($errors[1], $list->get(1));
    }

    /** @test */
    public function merging_lists (): void
    {
        $e1 = new ErrorStub();
        $e2 = new ErrorStub();

        $list1 = new ErrorList($e1);
        $list2 = new ErrorList($e2);

        $list3 = $list1->merge($list2);

        $this->assertNotSame($list3, $list1);
        $this->assertNotSame($list3, $list2);
        $this->assertSame($list3->get(0), $e1);
        $this->assertSame($list3->get(1), $e2);
    }

    /** @test */
    public function iterating (): void
    {
        $errors = [
            new ErrorStub(),
            new ErrorStub(),
            new ErrorStub(),
        ];

        $list = new ErrorList(...$errors);

        foreach ($list as $key => $value) {
            $this->assertSame($errors[$key], $value);
        }
    }
}
