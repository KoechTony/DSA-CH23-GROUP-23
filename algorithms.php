<?php
/**
 * SocialNetwork Lite - Core DSA Engine
 * Architecture: Modular Component (Separation of Concerns)
 * Contains: Merge Sort, Max-Heap (Priority Queue), and Adjacency List Graph
 */

// =========================================================================
// 1. MANDATORY SORTING ALGORITHM: MERGE SORT ($O(n \log n)$)
// =========================================================================
/**
 * Recursively splits and sorts the moderation queue array alphabetically 
 * by the author's username using a divide-and-conquer strategy.
 */
function mergeSort($array) {
    if (count($array) <= 1) {
        return $array;
    }

    // Find the midpoint and split the array in half
    $mid = current(array_chunk($array, ceil(count($array) / 2)));
    $left = $mid;
    $right = array_slice($array, count($mid));

    // Recursively break down and sort both halves
    $left = mergeSort($left);
    $right = mergeSort($right);

    // Merge the sorted structural components back together
    return merge($left, $right);
}

/**
 * Helper function to compare and merge two sorted subarrays.
 */
function merge($left, $right) {
    $res = array();
    
    while (count($left) > 0 && count($right) > 0) {
        // Perform a case-insensitive alphabetical comparison on usernames
        if (strcasecmp($left[0]['username'], $right[0]['username']) <= 0) {
            $res[] = array_shift($left);
        } else {
            $res[] = array_shift($right);
        }
    }
    
    // Catch any remaining element fragments
    while (count($left) > 0) {
        $res[] = array_shift($left);
    }
    while (count($right) > 0) {
        $res[] = array_shift($right);
    }
    
    return $res;
}


// =========================================================================
// 2. MANDATORY DATA STRUCTURE: MAX-HEAP (Priority Queue)
// =========================================================================
/**
 * A binary tree-mapped array structure that bubbles up items dynamically 
 * based on their calculated toxicity/urgency priority weights.
 */
class MaxHeap {
    private $heap = array();

    /**
     * Inserts an item into the heap matrix and restores heap properties ($O(\log n)$)
     */
    public function insert($item) {
        $this->heap[] = $item;
        $this->heapifyUp(count($this->heap) - 1);
    }

    /**
     * Extracts and removes the absolute highest priority element from the root ($O(\log n)$)
     */
    public function extractMax() {
        if (count($this->heap) === 0) return null;
        if (count($this->heap) === 1) return array_pop($this->heap);

        $max = $this->heap[0];
        $this->heap[0] = array_pop($this->heap);
        $this->heapifyDown(0);

        return $max;
    }

    public function isEmpty() {
        return empty($this->heap);
    }

    /**
     * Moves an element up the tree to maintain max-heap priority compliance
     */
    private function heapifyUp($index) {
        while ($index > 0) {
            $parentIndex = floor(($index - 1) / 2);
            
            // Compare node priority values
            if ($this->heap[$index]['priority'] > $this->heap[$parentIndex]['priority']) {
                $this->swap($index, $parentIndex);
                $index = $parentIndex;
            } else {
                break;
            }
        }
    }

    /**
     * Pushes an element down the tree if it violates max-heap properties
     */
    private function heapifyDown($index) {
        $lastIndex = count($this->heap) - 1;
        
        while (true) {
            $left = (2 * $index) + 1;
            $right = (2 * $index) + 2;
            $largest = $index;

            if ($left <= $lastIndex && $this->heap[$left]['priority'] > $this->heap[$largest]['priority']) {
                $largest = $left;
            }
            if ($right <= $lastIndex && $this->heap[$right]['priority'] > $this->heap[$largest]['priority']) {
                $largest = $right;
            }

            if ($largest !== $index) {
                $this->swap($index, $largest);
                $index = $largest;
            } else {
                break;
            }
        }
    }

    private function swap($a, $b) {
        $temp = $this->heap[$a];
        $this->heap[$a] = $this->heap[$b];
        $this->heap[$b] = $temp;
    }
}


// =========================================================================
// 3. MANDATORY DATA STRUCTURE: GRAPH (Adjacency List)
// =========================================================================
/**
 * Models the social connection architecture of the network as an Adjacency List
 * map to analyze interactions, clusters, and systemic user collusion risks.
 */
class SocialGraph {
    private $adjacencyList = array();

    /**
     * Provisions a unique structural user node inside the network map.
     */
    public function addUser($username) {
        if (!isset($this->adjacencyList[$username])) {
            $this->adjacencyList[$username] = array();
        }
    }

    /**
     * Establishes an unweighted bidirectional relationship link (edge) between nodes.
     */
    public function addRelationship($user1, $user2) {
        $this->addUser($user1);
        $this->addUser($user2);
        
        if (!in_array($user2, $this->adjacencyList[$user1])) {
            $this->adjacencyList[$user1][] = $user2;
        }
        if (!in_array($user1, $this->adjacencyList[$user2])) {
            $this->adjacencyList[$user2][] = $user1;
        }
    }

    /**
     * Checks if a direct relationship edge connects two user nodes ($O(k)$ neighbors)
     */
    public function isDirectlyConnected($user1, $user2) {
        if (isset($this->adjacencyList[$user1])) {
            return in_array($user2, $this->adjacencyList[$user1]);
        }
        return false;
    }
}