[https://github.com/PacktPublishing/Functional-PHP](https://github.com/PacktPublishing/Functional-PHP)

待翻译

1. Functions as First Class Citizens in PHP   
    1. Before we begin   
        * Coding standards   
        * Autoloading and Composer   
    1. Functions and methods   
    1. PHP 7 scalar type hints   
    1. Anonymous functions   
    1. Closures   
        * Closures inside of classes   
    1. Using objects as functions   
    1. The Closure class   
    1. Higher-order functions   
    1. What is a callable?   
    1. Summary   
2. Pure Functions, Referential Transparency, and Immutability   
    1. Two sets of input and output   
    1. Pure functions   
        * What about encapsulation?   
        * Spotting side causes   
        * Spotting side effects   
        * What about object methods?   
        * Closing words   
    1. Immutability   
        * Why does immutability matter?   
        * Data sharing   
        * Using constants   
        * An RFC is on its way   
        * Value objects   
        * Libraries for immutable collections   
            * Laravel Collection   
            * Immutable.php   
    1. Referential transparency   
        * Non-strictness or lazy evaluation   
            * Performance   
            * Code readability   
            * Infinite lists or streams   
        * Code optimization   
        * Memoization   
    1. PHP in all that?   
    1. Summary   
3. Functional Basis in PHP   
    1. General advice   
        * Making all inputs explicit   
        * Avoiding temporary variables   
        * Smaller functions   
        * Parameter order matters   
    1. The map function   
    1. The filter function   
    1. The fold or reduce function   
        * The map and filter functions using fold   
        * Folding left and right   
        * The MapReduce model   
    1. Convolution or zip   
    1. Recursion   
        * Recursion and loops   
    1. Exceptions   
        * PHP 7 and exceptions   
    1. Alternatives to exceptions   
        * Logging/displaying error message   
        * Error codes   
        * Default value/null   
        * Error handler   
    1. The Option/Maybe and Either types   
        * Lifting functions   
        * The Either type   
    1. Libraries   
        * The functional-php library   
            * How to use the functions   
            * General helpers   
            * Extending PHP functions   
            * Working with predicates   
            * Invoking functions   
            * Manipulating data   
            * Wrapping up   
        * The php-option library   
        * Laravel collections   
            * Working with Laravel's Collections   
        * The immutable-php library   
            * Using immutable.php   
        * Other libraries   
            * The Underscore.php library   
            * Saber   
            * Rawr   
            * PHP Functional   
            * Functional   
            * PHP functional programming Utils   
            * Non-standard PHP library   
    1. Summary   
4. Composing Functions   
    1. Composing functions   
    1. Partial application   
    1. Currying   
        * Currying functions in PHP   
    1. Parameter order matters a lot!   
    1. Using composition to solve real issues   
    1. Summary   
5. Functors, Applicatives, and Monads   
    1. Functors   
        * Identity function   
        * Functor laws   
        * Identity functor   
        * 5Closing words   
    1. Applicative functors   
        * The applicative abstraction   
        * Applicative laws   
            * Map   
            * Identity   
            * Homomorphism   
            * Interchange   
            * Composition   
            * Verifying that the laws hold   
        * Using applicatives   
    1. Monoids   
        * Identity law   
        * Associativity law   
        * Verifying that the laws hold   
        * What are monoids useful for?   
        * A monoid implementation   
        * Our first monoids   
        * Using monoids   
    1. Monads   
        * Monad laws   
            * Left identity   
            * Right identity   
            * Associativity   
            * Validating our monads   
        * Why monads?   
        * Another take on monads   
        * A quick monad example   
    1. Further reading   
    1. Summary   
6. Real-Life Monads   
    1. Monadic helper methods   
        * The filterM method   
        * The foldM method   
        * Closing words   
    1. Maybe and Either monads   
        * Motivation   
        * Implementation   
        * Examples   
    1. List monad   
        * Motivation   
        * Implementation   
        * Examples   
            * Where can the knight go?   
    1. Writer monad   
        * Motivation   
        * Implementation   
        * Examples   
    1. Reader monad   
        * Motivation   
        * Implementation   
        * Examples   
    1. State monad   
        * Motivation   
        * Implementation   
        * Examples   
    1. IO monad   
        * Motivation   
        * Implementation   
        * Examples   
    1. Summary   
7. Functional Techniques and Topics   
    1. Type systems   
        * The Hindley-Milner type system   
        * Type signatures   
        * Free theorems   
        * Closing words   
    1. Point-free style   
    1. Using const for functions   
    1. Recursion, stack overflows, and trampolines   
    1. Tail-calls   
        * Tail-call elimination   
        * From recursion to tail recursion   
        * Stack overflows   
        * Trampolines   
            * Multi-step recursion   
            * The trampoline library   
        * Alternative method   
        * Closing words   
    1. Pattern matching   
        * Pattern matching in PHP   
            * Better switch statements   
            * Other usages   
    1. Type classes   
    1. Algebraic structures and category theory   
        * From mathematics to computer science   
            * Important mathematical terms   
        * Fantasy Land   
    1. Monad transformers   
    1. Lenses   
    1. Summary   
8. Testing   
    1. Testing vocabulary   
    1. Testing pure functions   
        * All inputs are explicit   
        * Referential transparency and no side-effects   
        * Simplified mocking   
        * Building blocks   
        * Closing words   
    1. Speeding up using parallelization   
    1. Property-based testing   
        * What exactly is a property?   
        * Implementing the add function   
        * The PhpQuickCheck testing library   
        * Eris   
        * Closing words   
    1. Summary   
9. Performance Efficiency   
    1. Performance impact   
        * Does the overhead matter?   
        * Let's not forget   
        * Can we do something?   
        * Closing words   
    1. Memoization   
        * Haskell, Scala, and memoization   
        * Closing words   
    1. Parallelization of computation   
        * Parallel tasks in PHP   
            * The pthreads extension   
            * Messaging queues   
            * Other options   
        * Closing words   
    1. Summary   
10. PHP Frameworks and FP   
    1. Symfony   
        * Handling the request   
        * Database entities   
            * Embeddables   
            * Avoiding setters   
            * Why immutable entities?   
            * Symfony ParamConverter   
            * Maybe there is an entity   
        * Organizing your business logic   
        * Flash messages, sessions, and other APIs with side-effects   
        * Closing words   
    1. Laravel   
        * Database results   
            * Using Maybe   
        * Getting rid of facades   
        * HTTP request   
        * Closing words   
    1. Drupal   
        * Database access   
        * Dealing with hooks requiring side effects   
        * Hook orders   
        * Closing words   
    1. WordPress   
        * Database access   
        * Benefits of a functional approach   
        * Closing words   
    1. Summary   
11. Designing a Functional Application   
    1. Architecture of a purely functional application   
    1. From Functional Reactive Animation to Functional Reactive Programming   
        * Reactive programming   
        * Functional Reactive Programming   
            * Time traveling   
            * Disclaimer   
        * Going further   
    1. ReactiveX primer   
    1. RxPHP   
        * Achieving referential transparency   
    1. Summary   
12. What Are We Talking about When We Talk about Functional Programming   
    1. What is functional programming all about?   
        * Functions   
        * Declarative programming   
        * Avoiding mutable state   
    1. Why is functional programming the future of software development?   
        * Reducing the cognitive burden on developers   
            * Keeping the state away   
            * Small building blocks   
            * Locality of concerns   
            * Declarative programming   
        * Software with fewer bugs   
        * Easier refactoring   
        * Parallel execution   
        * Enforcing good practices   
    1. A quick history of the functional world   
        * The first years   
        * The Lisp family   
        * ML   
        * The rise of Erlang   
        * Haskell   
        * Scala   
        * The newcomers   
    1. Functional jargon   