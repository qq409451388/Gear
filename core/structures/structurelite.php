<?php
interface Map
{
    public function clear():void;
    public function containsKey(string $k):bool;
    public function put(string $k, $v):void;
    public function get(string $k);
    public function equals(Map $targetMap):bool;
    public function hashCode():string;
    public function forEach(Closure $annoFunction);
    public function isEmpty():bool;
    public function keySet():Set;
    public function values():Collection;
    public function remove($index):void;
    public function replace():void;
    public function size():int;
}

abstract class AbstractMap implements Map
{

}

class HashMap extends AbstractMap
{
    private $dataSpace;
    protected const INIT_MAP_SIZE = 16;
    protected const THRESHOLD_LINKEDLIST_MAXSIZE = 16;

    public function __construct(){
        $this->dataSpace = new EzArray(self::INIT_MAP_SIZE);
    }

    public function clear():void {
        if(!$this->isEmpty()){
            $this->dataSpace->free();
        }
    }

    public function containsKey(string $k): bool {

    }

    public function put(string $k, $v): void
    {
        $hashCode = EzObject::hashCode($k);
        if(isset($this->dataSpace[$hashCode])){
            if($this->dataSpace[$hashCode] instanceof EzLinkedList){
                if($this->dataSpace[$hashCode]->size() > self::THRESHOLD_LINKEDLIST_MAXSIZE){
                    $this->convertToBlackRedTree($hashCode);
                    $this->put($k, $v);
                    return;
                }
                $this->dataSpace[$hashCode]->append($v);
            } else if ($this->dataSpace[$hashCode] instanceof EzBlackRedTree) {
                $this->dataSpace[$hashCode]->append($v);
            } else {
                DBC::throwEx("[HashMap Exception] Unknow Object DataType With ".gettype($this->dataSpace[$hashCode]));
            }
        }else{
            $this->dataSpace[$hashCode] = new EzLinkedList($v);
        }
    }

    private function convertToBlackRedTree($hashCode):void{
        $linkedList = $this->dataSpace[$hashCode];
        DBC::assertTrue($linkedList instanceof EzLinkedList, "[HashMap Exception] Unknow Object DataType With ".gettype($this->dataSpace[$hashCode]));
        $blackRedTree = new EzBlackRedTree($linkedList->size());
        while($linkedList->hasNext()){
            $node = $linkedList->next();
            $blackRedTree->append($node->getData());
        }
        $this->dataSpace[$hashCode] = $blackRedTree;
    }

    public function get(string $k)
    {
        // TODO: Implement get() method.
    }

    public function equals(Map $targetMap): bool
    {
        // TODO: Implement equals() method.
    }

    public function forEach(Closure $annoFunction)
    {
        // TODO: Implement forEach() method.
    }

    public function isEmpty(): bool
    {
        // TODO: Implement isEmpty() method.
    }

    public function keySet(): Set
    {
        // TODO: Implement keySet() method.
    }

    public function values(): Collection
    {
        // TODO: Implement values() method.
    }

    public function remove($index): void
    {
        // TODO: Implement remove() method.
    }

    public function replace(): void
    {
        // TODO: Implement replace() method.
    }

    public function size(): int
    {
        // TODO: Implement size() method.
    }

    public function hashCode(): string
    {
        // TODO: Implement hashCode() method.
    }
}

class LinkedHashMap extends HashMap
{

}

interface SortedMap extends Map
{

}

class TreeMap extends AbstractMap implements SortedMap
{

}

interface ConcurrentMap extends Map
{

}

class ConcurrentHashMap implements ConcurrentMap
{

}

class HashTable implements Map
{

}

interface EzIterable
{

}

interface Collection extends EzIterable
{

}

abstract class AbstractCollection implements Collection
{

}

interface Set extends Collection
{

}

interface SortedSet extends Set
{

}

abstract class AbstractSet extends AbstractCollection
{

}

class HashSet extends AbstractSet
{

}

class LinkedHashSet extends AbstractSet
{

}

class CopuOnWriteArraySet extends AbstractSet
{

}

class TreeSet implements SortedSet
{

}

interface EzList extends Collection
{

}

abstract class AbstractList implements EzList
{

}

class ArrayList extends AbstractList
{

}

class Stack extends AbstractList
{

}

abstract class AbstractSequentiaList extends AbstractList
{

}

interface Deque extends Queue
{

}

class  LinkedList extends AbstractSequentiaList implements Deque
{

}

interface Queue extends Collection
{

}

class ArrayDeque extends AbstractCollection implements Deque
{

}

abstract class AbstractQueue extends AbstractCollection implements Queue
{

}

class PriorityQueue extends AbstractQueue
{

}