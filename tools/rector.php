<?php declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\For_\{ForRepeatedCountToOwnVariableRector, ForToForeachRector};
use Rector\CodeQuality\Rector\If_\{ConsecutiveNullCompareReturnsToNullCoalesceQueueRector, SimplifyIfElseToTernaryRector, SimplifyIfReturnBoolRector};
use Rector\CodeQuality\Rector\Ternary\{SimplifyTautologyTernaryRector, SwitchNegatedTernaryRector};
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector;
use Rector\CodingStyle\Rector\ClassMethod\{NewlineBeforeNewAssignSetRector, OrderAttributesRector};
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\
{CallUserFuncArrayToVariadicRector,
	CallUserFuncToMethodCallRector,
	CountArrayToEmptyArrayComparisonRector,
	VersionCompareFuncCallToConstantRector};
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\{RemoveUselessParamTagRector, RemoveUselessReturnTagRector};
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\{ChangeIfElseValueAssignToEarlyReturnRector, RemoveAlwaysElseRector};
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\{AddMethodCallBasedStrictParamTypeRector, ParamTypeByMethodCallTypeRector, ParamTypeByParentCallTypeRector};
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $config): void {
	// Import names with use statements
	$config->importNames(TRUE);
	$config->importShortClasses(FALSE);

	$config->sets([
		LevelSetList::UP_TO_PHP_81,
		PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
		PHPUnitSetList::PHPUNIT_100,
	]);

	$config->rules([
		AddArrayDefaultToArrayPropertyRector::class,
		AddClosureReturnTypeRector::class,
		AddMethodCallBasedStrictParamTypeRector::class,
		CallUserFuncArrayToVariadicRector::class,
		CallUserFuncToMethodCallRector::class,
		CountArrayToEmptyArrayComparisonRector::class,
		ChangeIfElseValueAssignToEarlyReturnRector::class,
		ChangeNestedForeachIfsToEarlyContinueRector::class,
		CompleteDynamicPropertiesRector::class,
		ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class,
		CountArrayToEmptyArrayComparisonRector::class,
		ForRepeatedCountToOwnVariableRector::class,
		ForToForeachRector::class,
		// NewlineAfterStatementRector::class,
		NewlineBeforeNewAssignSetRector::class,
		ParamTypeByMethodCallTypeRector::class,
		ParamTypeByParentCallTypeRector::class,
		RemoveAlwaysElseRector::class,
		RemoveDuplicatedCaseInSwitchRector::class,
		RemoveFinalFromConstRector::class,
		RemoveUnusedForeachKeyRector::class,
		RemoveUselessParamTagRector::class,
		RemoveUselessReturnTagRector::class,
		RemoveUselessVarTagRector::class,
		SimplifyIfElseToTernaryRector::class,
		SimplifyIfReturnBoolRector::class,
		SimplifyTautologyTernaryRector::class,
		SymplifyQuoteEscapeRector::class,
		StaticArrowFunctionRector::class,
		SwitchNegatedTernaryRector::class,
		TypedPropertyFromAssignsRector::class,
		VersionCompareFuncCallToConstantRector::class,
		WrapEncapsedVariableInCurlyBracesRector::class,
	]);

	$config->ruleWithConfiguration(OrderAttributesRector::class, [
		'alphabetically',
	]);

	$config->skip([
		ReadOnlyPropertyRector::class,
		RestoreDefaultNullToNullableTypePropertyRector::class,
	]);
};
