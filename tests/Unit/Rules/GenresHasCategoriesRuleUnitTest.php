<?php

namespace Tests\Unit\Rules;

use App\Rules\GenresHasCategoriesRule;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;


class GenresHasCategoriesRuleUnitTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new GenresHasCategoriesRule(
            [1, 1, 2, 2]
        );
        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);

        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $categoriesId);
    }

    public function testGenresIdValue()
    {
        $rule = new GenresHasCategoriesRule(
            []
        );
        $rule->passes('', [1, 1, 2, 2]);

        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('genresId');
        $reflectionProperty->setAccessible(true);

        $genresId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $genresId);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresIsArrayEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetRowsIsEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect());

        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGenres()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect(['category_id' => 1]));

        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2],
            ]));


        $this->assertTrue($rule->passes('', [1]));

        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2],
                ['category_id' => 1],
            ]));


        $this->assertTrue($rule->passes('', [1]));
    }

    /** @return MockInterface|GenresHasCategoriesRule  */
    protected function createRuleMock(array $categoriesId)
    {
        /** @var MockInterface|GenresHasCategoriesRule $res */
        $res = \Mockery::mock(GenresHasCategoriesRule::class, [$categoriesId]);
        return $res
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
