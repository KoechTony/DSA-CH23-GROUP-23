# SocialNetwork Lite - Content Moderation Control Panel
### Coursework Assignment Submission: Data Structures and Algorithms (DSA)
**Repository Identification:** DSA-CH23-GROUP-SOLO

---

## 📌 Problem Statement
Modern social connection platforms face immense scaling challenges when handling reported content. Relying purely on relational database (I/O) sweeps to filter reports creates heavy server bottlenecks under high traffic loads. 

Furthermore, standard chronological queues fail to prioritize urgent security matters (such as malicious scams), allowing harmful links to stay live while moderators handle low-risk infractions. Finally, basic linear feeds fail to identify coordinated platform manipulation, such as harassment rings or close friends submitting fraudulent reports to protect each other.

**SocialNetwork Lite** addresses these structural challenges by shifting data execution from the disk into volatile memory (RAM). By storing incoming data arrays in optimized computer science structures, the system processes moderation logic, real-time priorities, and network risk tracing with minimal server overhead.

---

## 🚀 System Features & Data Structure Implementations

1. **The Moderation Queue (FIFO Logic):** Standard inbound user reports are stored sequentially inside an optimized list. The panel processes these items using a strict **First-In, First-Out** timeline based on ingestion timestamps, ensuring fair and systemic report management.
2. **The Audit History Stack (LIFO Logic):** To track administrative actions, a transaction stack stores operations sequentially. Admin actions (deleting posts or dismissing reports) are pushed onto the history stack. Triggering the **Undo Last Admin Action** feature instantly pops the most recent item off the **Last-In, First-Out** stack to cleanly reverse database modifications.
3. **The Critical Alert Max-Heap (Priority Queue):** Incoming content text streams are passed through a toxicity scanner. Text patterns matching flag phrases (e.g., *scam*, *click here*) calculate high priority values. These arrays are organized into a binary tree **Max-Heap** where the highest priority element dynamically bubbles to the root node, presenting immediately in a high-visibility alert banner.
4. **The User Connection Graph (Adjacency List):** Platform interactions are mapped structurally as vertex nodes connected by friendship edges. When an admin reviews a card, the system runs an interaction sweep over the adjacency list. If a direct edge connects the author and the reporter, the interface flags an orange **Collusion Alert**, identifying targeted platform manipulation immediately.
5. **The Sorting Engine (Merge Sort):** To ensure a clean alphabetical workspace sorted by author username, the core array is organized using a native **Merge Sort** algorithm. This divide-and-conquer strategy runs at a guaranteed optimal temporal complexity of $O(n \log n)$.
6. **The In-Memory Search Engine:** Admins can query usernames or post content via the filtering tool. The engine sweeps the existing sorted memory array directly, avoiding expensive secondary SQL database index queries.

---

## 🗺️ System Architecture Diagram
