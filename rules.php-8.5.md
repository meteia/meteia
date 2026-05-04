# Elegant Objects for PHP 8.5

This document distills the technical programming philosophy of Elegant Objects (EO), adapted to idiomatic PHP 8.5. It emphasizes object-oriented design with interfaces and readonly classes, testing with native DOM and dependency mocking, decorator-based API construction, and core software engineering practices using PHP's built-in features such as constructor property promotion, readonly properties, and DOMDocument.

## Table of Contents
- [Software Engineering Practice](#software-engineering-practice)
- [Refactoring](#refactoring)
- [Build & Release](#build--release)
- [Testing](#testing)
- [Object-Oriented Design](#object-oriented-design)
- [Immutability](#immutability)
- [Error Handling](#error-handling)
- [API Design](#api-design)
- [Code Style](#code-style)

## Software Engineering Practice

### Master Foundational Prerequisites Before Advancing to Complexity
Always consume foundational works in a domain before attempting complex pieces, as comprehension depends on prerequisite knowledge. (2014-04-06)

**Why:** Complex concepts appear impossible to understand without prior mastery of basics, breaking the logical chain of learning and leading to wasted effort.

**How:**
- Identify dependencies and list prerequisite concepts before starting a new area.
- Sequence learning in strict dependency order, beginning with absolute basics.
- Apply this to code by mastering core PHP 8.5 language features and object-oriented principles before adopting advanced libraries or patterns.

### Treat Maintainability Violations as Bugs
A software bug is any violation of functional or non-functional requirements, including maintainability. (2015-06-11)

**Why:** Focusing only on incorrect behavior ignores non-functional requirements like maintainability and reusability. These qualities are critical because unmaintainable code becomes a liability—it can't be extended or fixed efficiently, forcing costly rewrites. A bug exists even if the software behaves correctly but its source code is messy, undocumented, or hard to understand.

**How:**
- Treat inconsistent formatting, missing documentation, and overly complex code as bugs that must be fixed.
- Write non-functional requirements for maintainability (e.g., "code must be understandable by an average PHP developer") and enforce them.
- During code reviews, flag violations of coding standards and design principles as defects, not just style preferences.
- Prioritize maintainability bugs equally with functional ones, since they impact long-term cost and agility.
- Use static analysis tools to detect complexity and style issues automatically.

### Prioritize Maintainability Over Functionality to Make Cheaper Bugs
Sacrifice functional completeness to keep code maintainable—good programmers create more, but cheaper, bugs. (2015-06-18)

**Why:** Bugs are inevitable, but maintainability bugs cost far more to fix than functional ones. Under time pressure, you must choose where to make mistakes. Good programmers err on the side of clean design, accepting small functional flaws so the software remains easy to change and extend later, reducing long-term cost.

**How:**
- Deliver working but incomplete features with a well-factored design, leaving room for later fixes.
- Reject quick hacks that corrupt the codebase; let a minor functional bug ship instead.
- Write tests that validate design integrity (e.g., loose coupling) first, functional correctness second.
- In code reviews, challenge structural compromises more than missing edge-case behavior.

### Accept That Programs Contain an Unlimited Number of Bugs
Any program has an unlimited number of bugs; never attempt to count or eliminate all of them. (2017-05-23)

**Why:** A bug is any deviation from user expectations or non-functional requirements, which are inherently unbounded. Since full specification is impossible, there will always be more defects than can be found. Focus on those with real business impact instead of chasing theoretical completeness.

**How:**
- Prioritize bug fixes strictly by user-facing damage and business value.
- Write tests only for scenarios that matter to the business, not for exhaustive coverage.
- Accept that shipping software means shipping with unknown bugs and manage risk accordingly.
- This complements rules on forecasting bug counts by emphasizing the infinite nature of defects.

### Treat Every Imperfection as a Bug
Report any deviation from ideal software—missing features, tests, documentation, or design flaws—as a bug. (2018-02-06)

**Why:** The more bugs found and fixed internally before users encounter them, the higher the perceived quality. Treating every imperfection as a bug incentivizes thoroughness and shifts quality left, even in stable products where hidden issues degrade maintainability.

**How:**
- Report missing functionality, tests, or documentation as bugs immediately.
- Flag suboptimal code, inconsistent design, or non-idiomatic naming for refactoring.
- Treat unstable or environment-specific test failures as bugs.
- Write clear, actionable bug reports that drive improvements in the codebase.

### Prioritize Delivery Speed Over Code Quality
Programmers must focus solely on closing tasks fast; the project enforces quality through automated barriers. (2018-03-06)

**Why:** When developers worry about quality, they slow down and fear mistakes. The project gains the most value when programmers push code rapidly while a strong quality wall (automated builds, static analysis, code reviews) rejects bad changes. This conflict between speed-driven work and quality-enforcing processes yields fast growth and high quality.

**How:**
- Cut corners and make changes smaller to close tickets quickly.
- Modify only the relevant units without studying the entire codebase.
- Do not feel responsible for overall quality; trust the project's automated checks.
- Ensure the project maintains a read-only master branch, high test coverage, and mandatory static analysis.

### Eliminate All Static Analysis Warnings Before Modifying Code
Resolve every IDE warning and static analysis violation before attempting to understand or refactor the code. (2018-04-10)

**Why:** Warnings signal sloppiness that obscures intent and structure. Cleaning them forces tracing of dependencies, identification of dead code, and internalization of the design, building trust in the codebase.

**How:**
- Load the project into an IDE or run tools such as PHPStan or Psalm with the strictest inspection profile.
- Fix every warning and error, beginning with the trivial ones, without altering behavior.
- Refactor solely to remove warnings.
- Repeat the process until the file or project shows zero warnings.

### Make Code Clear to Strangers, Not Just Clean
Code must be immediately understandable by any stranger, not just free of anti-patterns. (2018-09-12)

**Why:** Clean code removes dirt like large methods and tight coupling, but clarity ensures a newcomer can use it without help. A dirty but usable kitchen beats a clean one you can’t operate. Maintainability means a stranger can fix a bug in under an hour, which requires the code to speak their language, not the designer’s.

**How:**
- Have an outsider attempt a 30-minute bug fix and observe their confusion.
- Regularly expose your code to strangers and encourage them to report unclear parts.
- Treat any report of “unclear” as a bug equal to a functional defect.
- Write code that explains itself without needing the original author’s assistance.

### Demand Incremental, Microtasked Changes in Pull Requests
Never accept a giant pull request that rewrites architecture; demand incremental, microtasked changes instead. (2019-01-01)

**Why:** Enthusiastic programmers often lack microtasking skills, pushing massive rewrites that violate existing principles. Accepting such changes transfers architectural control to someone who can't manage collaborative, incremental evolution, leading to chaos.

**How:**
- When a newcomer suggests a rewrite, ask them to submit a small, focused ticket instead.
- Reject any pull request that changes architecture in a single large chunk, explaining the need for incremental delivery.
- Insist that improvements be broken into isolated, reversible steps that align with the current design.
- In PHP 8.5 projects, enforce small commits on feature branches and require each micro-change to pass the full test suite and static analysis before merging.

### Never Use Static Loggers
Always inject a `Log` object as a dependency through the constructor; never use static loggers or global logging instances. (2019-03-19)

**Why:** Static loggers function as hidden global state, making it difficult to isolate and test logging behavior for individual objects, especially in concurrent scenarios. Injecting a Log collaborator allows configurable, per-object logging destinations and simplifies mocking in tests.

**How:**
- Define a `Log` interface with methods for different log levels, such as `info(string $message): void` and `error(string $message, ?\Throwable $exception = null): void`.
- In `readonly` classes, inject the dependency using constructor property promotion: `public function __construct(private readonly Log $log) {}`.
- Provide a `NullLog` implementation that silently discards messages as a safe default.
- In unit tests, inject a capturing fake Log to assert on logged messages without affecting other tests.
- Reject any use of static methods, global variables, or framework singletons for logging; always pass the dependency explicitly.

### Reject Weak Code, Not Weak Tests
Code reviewers must find and explain problems in the source, not test the branch. (2019-12-03)

**Why:** Code review is part of the merge pipeline, like a linter—it protects the trunk from bad code. Running builds or manual tests is inefficient for reviewers; it’s slower and harder to explain findings. If a branch passes automated checks but still has bugs, the pipeline is weak, and the reviewer should report that weakness instead of doing the tester’s job.

**How:**
- Inspect the diff visually; identify the three most critical problems and explain them with line references and fix suggestions.
- If you suspect a bug that only execution reveals, stop the review and file a bug against the merge pipeline for insufficient automated tests.
- Never check out the branch, run builds, or test manually—delegate execution verification to testers and automated checks.
- Demand that each review ends with either fixed issues or well-argued rebuttals; “everything is OK” is not a completed review.

### Reduce Bug Reports to the Minimal Reproducible Code Snippet
When reporting a bug, submit only the simplest code that reproduces the defect. (2022-03-29)

**Why:** A minimal example allows the maintainer to locate the root cause quickly without debugging extraneous statements or operations.

**How:**
- Strip the failing code line by line until only the essential operations remain.
- Verify the reduced snippet still produces the exact incorrect behavior.
- In PHP 8.5, provide a self-contained script using only the relevant `readonly` classes and interfaces.

### Never Code Without an Automated Test Safety Net
Automated tests are your safety net—without them, every change risks catastrophic failure; you must build and strengthen them before writing any code. (2022-07-05)

**Why:** Tests reduce routine work, give confidence to refactor and add features, and catch bugs before they reach customers. A weak net lets errors fall through, causing server downtime, lost money, and damaged trust. The stronger your net, the faster and better you code.

**How:**
- Automate the build pipeline and create a few basic tests before writing any production code.
- Run all tests locally after each change; ensure they execute automatically on every merge to trunk.
- When a bug is reported, first add a failing test that reproduces it, then fix the code.
- Continuously increase test coverage to seal holes in your safety net.

### Separate Test Edits from Code Changes in Pull Requests
Always put test modifications in one pull request first, and only then the code changes—never the two together. (2022-08-04)

**Why:** Reviewers validate requirements purely in the test PR, free from implementation bias. Then in the code PR, they know tests are fixed and won’t be silently adjusted to fit flawed code. This prevents “listening to tests” by tuning them after implementation, and ensures each review focuses on a single aspect—intent or realization.

**How:**
- First PR: add, modify, or disable tests; merge this even if it temporarily breaks the build.
- Second PR: implement the code that makes the disabled tests pass; never alter test bodies or add new test logic here.
- Reviewers: in the test PR, ask “Do we really need this functionality?”; in the code PR, ask “Does this implementation fulfill the requirements correctly?”
- When merging the second PR, remove any disable markers only where tests now pass; a CI pipeline guards against regressions.

### Store CLI Defaults as Plain Text Arguments
Use plain text files for command-line defaults, not structured formats. (2022-07-20)

**Why:** Users shouldn't learn two formats—one for CLI arguments and another for configuration. Plain text files mirror command-line syntax exactly, reducing cognitive load and making defaults interchangeable with manual invocation. This approach avoids the complexity and parsing overhead of YAML, JSON, or TOML.

**How:**
- Create a dotfile (e.g., `.tool`) in the project root with one argument per line, using `--option=value` syntax.
- Design your PHP CLI tool to read this file and concatenate its lines with any command-line arguments provided at runtime via `$argv`.
- Support global defaults via a home-directory file (e.g., `~/.tool`) that merges with local and explicit options.

### Delegate Mundane Code Chores to AI Assistants
Offload tedious programming tasks—bug reporting, refactoring, documentation—to AI because humans neglect them. (2023-08-29)

**Why:** Humans are lazy with routine duties like writing proper bug reports, documenting code, and following conventions. AI robots will do these tasks incrementally and consistently, improving readability and maintainability without changing functionality. This frees developers for creative work and ensures a disciplined codebase.

**How:**
- Integrate AI tools to automatically review pull requests and suggest improvements in PHP 8.5 syntax, such as recommending `readonly` classes, constructor property promotion, or decorator compositions.
- Use AI to generate and refine bug reports, PHPDoc blocks, and micro-refactorings.
- Merge robot-submitted pull requests that make small, safe quality enhancements.
- Let AI prioritize backlogs, detect technical debt, and keep architecture docs in sync.

### Follow Team Coding Standards Instead of Personal Preferences
Your coding style is irrelevant; follow team standards. (2023-10-01)

**Why:** Individualism in code creates unmaintainable, fragmented systems because most programmers cannot self-regulate quality. Collective standards, like enforced conventions and reviews, provide the consistency that turns a group of coders into a stable, productive team.

**How:**
- Automate style checks with linters and static analysis tools (such as PHPStan or Psalm) in your CI pipeline.
- Make peer reviews mandatory for every merge, focusing on convention adherence.
- Reject personal formatting preferences or "clever" code that deviates from team rules.
- Treat code as team property; anyone can refactor to align with standards.

### Challenge Every Software Convention with Evidence
Treat every established practice as a hypothesis that must earn its place through fair, evidence-based scrutiny, not through tradition. (2024-04-02)

**Why:** Just as historical narratives crumble under cross-examination, software "best practices" often persist only because nobody challenges them. Developers must act like impartial judges, hearing all sides before committing to a design. This prevents cargo-culting and uncovers hidden assumptions that lead to brittle systems.

**How:**
- During code reviews, demand that every architectural choice be defended with evidence, not just authority or habit.
- Before adopting a library or pattern in PHP 8.5, require at least two viable alternatives to be evaluated against explicit criteria such as performance with `readonly` classes or testability.
- Hold regular design discussions where team members argue for and against current conventions.

### Phrase Bug Report Titles as Direct Complaints About Broken Behavior
Always formulate issue titles as explicit complaints stating what is broken, even for feature requests. (2025-05-31)

**Why:** A complaint makes the gap between expectation and reality explicit from the first word, eliminating ambiguity and forcing immediate clarity on the defect.

**How:**
- Replace neutral titles like “CSV downloading” with “PNG downloading is broken, returning CSV instead”.
- For missing features, use “Cannot export reports to PDF” rather than “Add PDF export feature”.
- In PHP 8.5 projects, apply this consistently in issue trackers so that every report drives a concrete, verifiable fix.

### Accompany Every Code Change with an Automated Test
Every modification to production code must include an automated test that validates the change and protects existing behavior. (2025-06-08)

**Why:** Tests act as a permanent warranty on the codebase. Without them, refactoring or extending previously verified functionality risks silent breakage, wasting the investment already made in working code.

**How:**
- Never submit a feature or bug-fix pull request without at least one new or updated test method.
- Execute the full test suite before merging to confirm no regression of previously tested behavior.
- In PHP 8.5, place the new test in the corresponding test class using descriptive method names and assert on the exact outcome.

### Close Pull Requests That Encounter Non-Trivial Merge Resistance
If a pull request meets resistance beyond trivial style fixes, close it immediately and treat the resistance as a defect in the repository. (2025-11-09)

**Why:** Difficulty merging signals incomplete documentation, unclear architecture, or outdated information in the main codebase, not a problem with the proposed change. Continuing to fight the merge wastes time and damages reputation.

**How:**
- Close the pull request as soon as non-style objections appear.
- File a separate bug report against the master branch describing the unclear part or missing information.
- Extract only the uncontested fragments into smaller, focused pull requests.
- In PHP 8.5 projects, ensure that `readonly` classes and interfaces are self-documenting so future merges remain smooth.

## Refactoring

### Refactor Incrementally Instead of Rewriting
Refactor systems gradually by decoupling core dependencies one component at a time rather than attempting a complete rewrite. (2019-09-15)

**Why:** Sudden replacements preserve the same flawed structures and dependencies; incremental refactoring shifts the system toward sustainable, productive designs over time.

**How:**
- Identify central dependencies such as god classes and extract them into independent modules.
- Reward the creation of new, decoupled components.
- Replace components sequentially, verifying each step reduces overall complexity.
- Adopt a long-term roadmap for incremental improvements instead of single large releases.
- In PHP 8.5, use small `readonly` classes, interfaces, and decorators to gradually replace procedural or monolithic code.

## Build & Release

### Fail Builds on Static Analysis Violations
Every build must pass all static analysis checks; any violation must cause an immediate build failure. Reject automated code formatters and force developers to manually fix violations so they internalize the rules. (2014-08-13, 2018-01-16)

**Why:** Strict static analysis ensures consistent, safe design principles, making the codebase predictable and easier to maintain while eliminating hidden flaws early. Manual fixes embed the project's object-oriented philosophy and formatting discipline, turning maintainability into a personal habit rather than a tool's afterthought.

**How:**
- Integrate static analysis into the CI pipeline with zero tolerance for violations.
- Configure the pipeline to reject any changes that introduce issues.
- Leverage PHP 8.5 features such as strict types, readonly classes, and attributes to improve analyzability.
- Require pull request authors to manually resolve every violation before merge; pair each with a concise explanation of the underlying principle.
- Never provide a one-click reformatting script; let the repetition of fixes teach the standard.
- Fix all violations before submitting changes.

### Protect the Master Branch with Automated Validation
Make the master branch read-only and ensure all merges occur only through an automated script after the full test suite passes. (2014-10-05, 2014-10-08)

**Why:** Direct commits to master bypass validation, producing broken builds and eroding team discipline. Automated pre-merge gates keep the main branch permanently green and remove the fear of disrupting shared code.

**How:**
- Enable branch protection rules to prohibit direct pushes to master.
- Implement a merge automation script that checks out the feature branch, executes the complete build (including all unit tests and static analysis), and merges only on success.
- Route every change through feature branches; never commit manually to master.

### Automate Every Release to a Single Command
The entire release process must be fully automated and executable from the command line with zero manual intervention. (2015-06-08)

**Why:** Ad hoc releases introduce magic steps and human dependency, making the project fragile and unmaintainable. A new developer must be able to release instantly without tribal knowledge. Automation eliminates variability and ensures every release is repeatable and traceable.

**How:**
- Script the entire pipeline—build, test, package, deploy—into a single executable file (e.g., `release.php`).
- Ensure the script can be run by anyone with appropriate permissions, failing loudly on any error.
- Integrate with CI/CD to trigger releases only through the automated path.
- Never allow manual steps, logins, or ad-hoc checks during a release.

### Document Decisions, Assumptions, and Risks in Prototypes with Fragile CI
Architect a prototype by documenting every decision, assumption, and risk, then wrapping it in a deliberately fragile continuous integration pipeline. (2015-08-04)

**Why:** Without traceable decisions and automated quality gates, software becomes an untestable pile of files. A fragile build fails on any static analysis, formatting, or coverage drop, preventing future programmers from introducing chaos. Explicit documentation forces accountability and exposes unknowns early.

**How:**
- Write key architectural choices and rejected alternatives in README.md with short, opinionated justifications.
- List each assumption and risk (with probability/impact scores) to document gaps and potential failures.
- Configure CI to fail on any static analysis violation, formatting error, or drop in test coverage below a strict threshold.
- Set up one-click continuous delivery so the prototype is always deployable as a working product.

### Automate Every Build Step from Commit to Production Simulation
Your CI pipeline must progress through maturity levels, automating every build step from commit to production simulation in containers. (2016-08-01)

**Why:** Each level eliminates human error and delays, ensuring that every change is validated thoroughly before reaching users. Skipping levels leaves gaps where defects can hide, undermining the reliability of your software.

**How:**
- Automate the build into a single command.
- Move code to Git and enforce pull requests with mandatory code reviews.
- Add unit tests and static analysis to the build, failing it on any quality or coverage drop.
- Implement pre-flight builds and production simulation in containers.
- Automate stress tests on every build.

### Enforce Strict XML Formatting with Automated Style Checking
Always validate XML formatting style, not just its structure, and fail builds on violations. (2017-08-29)

**Why:** XML is human-readable only when properly formatted; a badly styled document is as unreadable as messy code. Automated enforcement prevents sloppy formatting from degrading readability and maintainability.

**How:**
- Integrate an automated XML style checker into the CI pipeline.
- Configure the checker to enforce consistent indentation, structure, and license headers.
- Use the checker to automatically correct formatting where possible.
- Fail the build on any remaining style violations.

### Make Code Ready for Reuse
Ensure code is packaged, documented, licensed, and quality-gated so it can be reused by other developers. (2018-05-08)

**Why:** Merely publishing source code is insufficient for reuse; without packaging, documentation, and automated checks, integration wastes time and damages reputation.

**How:**
- Write a README.md that explains purpose and usage immediately.
- Choose a permissive license such as MIT and publish the package to Packagist.
- Document every public class and method with PHPDoc blocks containing usage examples.
- Configure CI to run tests, static analysis (e.g., PHPStan), and other quality checks automatically.
- Include contribution guidelines and status badges.

### Cap Repository Size to Avoid Monolithic Codebases
Never allow a single PHP repository to exceed 50,000 lines of code; decompose larger codebases into small, focused, standalone repositories with independent build processes, strict linting, and fast test suites. (2018-09-05, 2025-11-16)

**Why:** Large repositories foster tight coupling, slow builds, and untestable code. Small repositories enable stricter style enforcement, deeper testing without slow builds, more thorough code reviews, clearer documentation, frequent releases, and stronger code ownership.

**How:**
- When a repository approaches the limit, extract a cohesive module into its own PHP project with its own `composer.json`, build script, and CI pipeline.
- In each small repository, enable maximum PHPStan rules and aim for fast, comprehensive integration tests.
- Write a single, focused README.md that defines the exact scope to prevent feature creep and duplication.
- Consider open-sourcing extracted repositories to gain external scrutiny and contributor accountability.
- Monitor line count and build time metrics to trigger splits proactively.

### Use Exact Versions for Untrusted Dependencies
Only use dynamic version ranges for libraries whose authors you trust to follow semantic versioning; fix exact versions for all others. (2019-01-29)

**Why:** Fixed versions prevent breaking changes but cause conflicts when transitive dependencies require different versions. Dynamic ranges avoid conflicts but risk breakage from careless authors. Trusting a library means you believe it won’t break backward compatibility without a major version bump, so you can safely stay flexible.

**How:**
- Review each dependency’s source code and release history before trusting it dynamically.
- In `composer.json`, pin the exact version (e.g., `"1.13.5"`) for any library you haven’t personally vetted.
- Use dynamic ranges (e.g., `"^1.2"`) only for libraries you’ve audited and whose authors you trust.
- Periodically update fixed dependencies manually to prevent staleness and security gaps.
- Use Composer to manage and audit dependencies in the build pipeline.

### Implement Tiered Builds for Rapid Feedback and Thorough Validation
Implement at least two builds (fast local and thorough CI) and ideally four: fast, cheap, preflight, and proper. (2025-04-12)

**Why:** A single build cannot serve both rapid coding feedback and exhaustive quality gates. Fast builds keep the rhythm of energetic development fun, while thorough builds prevent bugs that quick unit tests miss; mixing them kills joy or lets defects through.

**How:**
- Run a fast build locally in under 10 seconds with only unit tests using PHPUnit and coverage checks.
- Set up cheap automated workflows under 10 minutes for integration tests and style checks after every push.
- Trigger a preflight build on merge (up to an hour) with mutation testing.
- Reserve a proper build for releases, running multi-environment and regression tests without time limits.
- In PHP 8.5 projects, use scripts that invoke `php bin/phpunit` for fast levels and full static analysis with PHPStan for deeper levels.

### Only Merge Build-Fixing Changes into a Broken Master Branch
Never merge feature changes into a broken master branch; only merge build-fixing changes. (2025-04-19)

**Why:** A broken CI build increases the cost of diagnosing and fixing failures when additional changes are piled on. The team must keep the master branch always green to ensure any new changes are tested against a stable baseline. Ignoring broken builds leads to prolonged delays and accumulation of technical debt.

**How:**
- Verify that all automated checks are green before you start making code edits.
- If any job is red, do not begin feature work; report the failure or fix the build yourself in a separate pull request.
- Submit build fixes only, never mix them with feature or bugfix changes.
- Wait for the master branch to become fully green before submitting any new feature pull requests.
- Use branch protection rules to enforce this policy.

## Testing

### Validate HTML Output in Unit Tests
Always validate HTML output in unit tests by parsing it with the DOM parser and inspecting the resulting structure. (2014-04-06)

**Why:** Parsing and querying the DOM catches structural issues, missing elements, and malformed markup that static checks miss, ensuring the output behaves correctly.

**How:**
- Load the HTML string into a `DOMDocument` instance inside the test.
- Create a `DOMXPath` object to inspect elements, attributes, and content using XPath queries.
- Assert on the parsed structure and skip the test gracefully if parsing fails.

### Mock the HTTP Server When Testing HTTP Clients
Always mock the HTTP server when testing HTTP clients. (2014-04-18)

**Why:** Real HTTP servers introduce network latency, external dependencies, and non-determinism that make tests unreliable and slow.

**How:**
- Set up a controlled mock server before the test to queue expected responses with specific status codes, headers, and bodies.
- Direct the HTTP client under test to the mock server's URL.
- After the request, inspect the captured request details to verify headers, method, and body.
- Shut down the mock server after the test completes.

### Wrap XML Parsing in a Concise Interface
Always wrap low-level XML and DOM parsing in a simple object interface to eliminate verbose traversal code. (2014-04-24)

**Why:** Direct use of `DOMDocument` and `DOMXPath` produces repetitive boilerplate even for basic queries, obscuring business logic.

**How:**
- Create a narrow interface exposing only `xpath(string $query): string` and `nodes(string $query): array` methods.
- Implement the wrapper by delegating to an internal `DOMDocument` instance.
- Use the wrapper in both production code and tests for readable XPath access without manual node handling.

### Use XPath Assertions for XML and HTML in Tests
Use `DOMXPath` queries directly for assertions on XML and HTML output to keep tests declarative and avoid manual node conversion. (2014-04-28)

**Why:** String-based or manual DOM checks are fragile and verbose; XPath allows precise, readable verification of structure and content.

**How:**
- Load the output string into `DOMDocument`.
- Instantiate `DOMXPath` and execute queries to retrieve nodes or values.
- Assert on node counts, attribute values, or text content in a single test statement.

### Test Database Integrations Against Real Servers
Always test database access code against a real database instance during integration tests rather than mocks or in-memory substitutes. (2014-05-01, 2014-05-21)

**Why:** Mocks and simplified engines hide dialect differences, transaction behavior, and constraint enforcement that only appear with the actual server.

**How:**
- Connect via `PDO` or the native driver to a locally running database instance configured for tests.
- Execute real queries and assertions inside the integration test phase.
- Isolate the test database with unique schema or containers to ensure repeatability.
- Bootstrap the database instance before tests begin and ensure automatic cleanup afterward to maintain repeatability without manual intervention.

### Bootstrap Real Database Instances for Integration Tests
Bootstrap a real database instance before running integration tests and ensure automatic cleanup afterward. (2017-06-13)

**Why:** Mocks hide integration issues; real instances reveal dialect, transaction, and constraint behaviors. Automation ensures repeatability without manual steps.

**How:**
- Use process control or CI configuration to launch a local database server accessible via `PDO`, poll for readiness, execute tests, and terminate the instance after completion.
- Configure connection details dynamically for the test environment.
- Ensure the database is isolated per test run to prevent state leakage.

### Stop Testing at the Forecasted Bug Count
Testing must stop only after a predefined number of bugs are found, not when testers believe the product is bug-free. Forecast the target bug count based on acceptable risk before testing begins. (2014-08-22, 2015-09-10)

**Why:** Software contains an unlimited number of bugs, so proving correctness is impossible. Testing is a destructive process aimed at finding errors, not confirming quality. Releasing with known and unknown bugs is inevitable; the goal is to reach a level of confidence that the discovery rate is acceptable.

**How:**
- Before testing begins, forecast the number of bugs based on acceptable risk.
- Track discovered bugs against the forecast; stop testing immediately once the count is reached.
- Instruct that the sole mission is to break the product and report errors.
- Release consciously, accepting undiscovered bugs remain.
- Use your test suite to count defects found during the process.

### Measure Software Quality by the Ratio of Internally Discovered Bugs to Total Bugs
Track quality as the number of bugs found before release divided by the total bugs found (internal plus user-reported); maximize internal discovery to improve the ratio. (2017-12-26)

**Why:** With an infinite number of bugs, the only controllable factor is catching more defects internally before users encounter them. This metric quantifies testing effectiveness and guides investment in reviews, static analysis, and test design.

**How:**
- Maintain separate tracking for pre-release defects (F) and post-release user reports (U).
- Compute quality as F / (F + U) and set improvement targets.
- Combine with forecasted bug counts to decide when to stop testing.
- Use the metric during retrospectives to refine test coverage and static analysis rules.

### Include Fake Objects in the Library Distribution and Keep Test Classes Pure
Ship fake implementations of interfaces alongside production code to simplify testing. Always scaffold tests with reusable fake objects shipped in production code, never with setup/teardown methods or private static utilities. A test class must contain only test methods; all prerequisites must be fake objects living in the main source tree. (2014-09-23, 2015-05-25, 2023-01-19)

**Why:** Complex mocking hierarchies obscure test intent and increase maintenance cost; built-in fakes provide readable, reusable behavior that aligns with the real API. Test fixtures coupled in setup methods break isolation between test methods, causing changes to ripple. Static utility methods are anti-OOP and duplicate across test classes. Mixing helpers into test classes breaks the direct mapping from test class names to live classes.

**How:**
- Create fake classes that implement the same interfaces with simplified, in-memory logic.
- Design fakes to accept test data directly via constructors.
- Use fakes in unit tests and expose them to library consumers.
- Place fake classes in the main source directory alongside production code.
- In each test method, instantiate the fake directly with required data; avoid shared setup methods or static helpers.
- Implement cleanup logic (e.g., via destructors or explicit close methods) for resources like temporary files.
- Remove all private static methods, attributes, and utility logic from test classes.
- Write separate tests for those fake objects to ensure their correctness.
- Use the test class suffix only for classes that directly test a corresponding live class.

### Write Tests Only After Users Report Bugs
Do not write tests upfront. Deploy code to users, wait for bug reports, then write a test that reproduces the bug before fixing it. Replace bug reports with pull requests containing a disabled unit test. (2017-03-24, 2023-07-25)

**Why:** Testing everything upfront is impossible and wasteful. The business only cares about visible, intolerable bugs. Creating a test after a bug is reported ensures the same mistake never recurs, and fixes only what someone paid to fix—once and for all. A disabled test gives maintainers an executable reproduction of the bug, saving them the effort of writing one themselves.

**How:**
- Deploy new features and major refactorings with zero tests, letting users or testers find bugs.
- When a bug is reported, write an automated test that fails because of the bug, mark it as disabled, and submit as a single pull request.
- Add a puzzle comment above the test describing the expected behavior.
- Fix the production code only after the failing test proves the bug exists.
- Let the test suite grow organically as bugs accumulate, rather than writing tests in advance.
- Address the auto-generated ticket in a separate PR.

### Reject Bug Fixes That Degrade Code Coverage
Never accept a bug fix that removes or disables unit tests without replacing them. (2015-06-22)

**Why:** Unit tests exist to prevent breaking the product under pressure. Removing them to pass a build hides the real problem and increases disorder in the code base. A fix that degrades coverage is a disservice—it conceals underlying quality issues instead of resolving them.

**How:**
- Require every bug fix to include a new unit test that reproduces the bug before the fix is applied.
- Reject any patch that comments out, deletes, or marks tests as skipped without adding equivalent or better coverage.
- If a hotfix disables tests temporarily, immediately schedule a follow-up task to restore and correct those tests.
- Treat a drop in code coverage as a blocker for merging, no matter how urgent the fix seems.

### Replace Debugging with Unit Tests and Refactoring
Unit testing completely replaces debugging; if debugging is necessary, the design is bad. (2016-02-09)

**Why:** Debugging targets symptoms without preventing recurrence; it wastes time on fragile, procedural code. Unit tests capture behavior eternally, exposing design flaws and enforcing clean, object-oriented structure. When testing is hard, the code is too intertwined—refactoring is mandatory, not optional.

**How:**
- Break static, procedural methods into small `readonly` classes with single responsibilities, using noun names (e.g., `Text`, `Words`) to define what they represent.
- Write unit tests for each class immediately; if a test is difficult, simplify the class until it is trivially testable.
- Use fakes for external dependencies (like file I/O) so tests never touch disk, network, or system state.
- Resist any urge to step through code; whenever a bug appears, encode it as a test first, then fix it.

### Make Each Test Method Independent
Each test method must own its own data and share no state or constants with other tests. Never rely on data left behind by other tests; always create fresh data inside the test. (2016-05-03, 2018-12-11)

**Why:** Shared setup or constants create coupling between tests, so changes in one test can break others unexpectedly. When one test generates data that another test consumes, execution order determines success or failure. This coupling makes tests fragile, non-deterministic, and impossible to run in isolation.

**How:**
- Inline test literals directly in each test method instead of using class constants.
- Instantiate all objects fresh inside the test method; avoid `setUp()` for shared fixtures.
- Use distinct values in each test to highlight their independence.
- Structure tests so each method is self-contained.
- Always set up required data inside the test method or its dedicated setup routine.
- Never read from a shared database, file, or global state that another test might modify.
- Use fresh, disposable resources (in-memory databases, temporary files) for each test run.
- Run tests in random order to expose accidental data dependencies.

### Make Every Test Method Contain a Single Assertion
Each test method must contain nothing but a single assertion; move all preparation logic into dedicated immutable objects. (2017-05-17)

**Why:** This forces tests to be declarative and object-oriented, improving reusability of test components, brevity, and readability. It drives production code toward immutability by eliminating algorithmic setup in tests.

**How:**
- Extract all setup and data-preparation code into helper `readonly` classes that are immutable and reusable across tests.
- Design these helpers to accept data via constructors and provide the prepared state through methods.
- In test methods, instantiate the helper and perform only the assertion.
- Use custom objects for complex assertions to keep the test method minimal.

### Design Production Code to Be Broken by Tests
Design production code to be easily broken by future tests rather than anticipating and satisfying them in advance. (2019-07-02)

**Why:** A test that always passes is useless; a good test must be able to fail. If you code to make future tests pass, you weaken test effectiveness and lower code quality. The code must help tests expose its flaws, because a test that easily turns red is a strong validation tool.

**How:**
- Write the simplest code that makes the current test pass, without anticipating future tests.
- Avoid adding logic or abstractions just to accommodate hypothetical test scenarios.
- Regularly review tests to ensure they can fail when the code changes incorrectly.
- Treat any test that never fails as a candidate for removal or strengthening.
- In PHP 8.5, structure small `readonly` classes with clear single-responsibility methods so that tests can reliably detect incorrect modifications.

### Never Log in Unit Tests; Assert Instead
Replace all logging statements in unit tests with explicit assertions that encode your visual verification. (2021-08-11)

**Why:** Logging in tests is a lazy substitute for proper assertions. It captures your momentary visual confirmation but robs future developers of that knowledge, leaving them with console noise they can't interpret. A passing test should produce no output—only failures should reveal details.

**How:**
- Delete every `Logger::debug(...)`, `echo`, or `var_dump(...)` call from test methods.
- Translate what you visually checked into a concrete assertion (e.g., `assertEquals`, `assertStringContainsString`, or DOM XPath checks).
- Use assertion messages or test failure output to reveal data only when the test fails.
- Refactor multi-statement tests into single-statement tests with a single assertion.

### Categorize Tests into Fast Local Checks and Deep CI Probes
Categorize automated tests into fast checks for local development and deep probes for CI, not unit versus integration. (2023-08-22)

**Why:** Fast tests give programmers immediate confidence when editing code, preventing frustration and the temptation to delete slow tests. Deep tests, though inherently slow because they involve external resources, uncover elusive bugs that mocks would hide. Running them on servers keeps the local build fast while still guaranteeing thorough validation.

**How:**
- Tag tests with PHP 8.5 attributes such as `#[Fast]` or `#[Deep]` based on execution time (e.g., under 20ms is fast).
- Configure local development scripts to run only fast tests by default.
- Execute deep tests exclusively in the CI pipeline.
- Only run deep tests on your laptop when CI reports a failure.

## Object-Oriented Design

### Design Classes as Real-Life Entity Representatives
Every class must model a real-life entity as an anthropomorphized representative with its own life cycle and behavior. Avoid names ending in `-er`, `-or`, `Manager`, `Validator`, `Utils`, `Controller`, `Parser`, `Service`, or `Factory`. Name objects by what they are, not what they do. Name methods by the noun they return or the verb they perform, without `get` or `set` prefixes. Never use getter or setter methods. Encapsulate the smallest possible real-life entity through the primary constructor arguments. (2014-04-27, 2014-09-16, 2014-11-20, 2014-12-15, 2015-03-09, 2015-05-28)

**Why:** Non-entity names indicate procedural thinking and produce rigid, hard-to-understand code. Getters and setters reduce objects to passive data structures, violating encapsulation. Objects that don’t represent real-life entities are artificial constructs invented solely to tie other objects together. Encapsulating a smaller entity yields a more solid, cohesive design than representing the entire universe. Classes ending in "-er" turn objects into dumb procedures that execute commands without encapsulating intent; true objects act as smart partners that can optimize their behavior.

**How:**
- Before creating a class, ask: “What real-life entity does this object represent?” If you can’t name one, refactor.
- Rename classes to entity names (e.g., `User` instead of `UserManager`, `Sorted` instead of `Sorter`).
- Use method names such as `name(): string` or `save(File $file): void`.
- Provide exactly one primary constructor that initializes all fields with entity-identifying data; any additional constructors or factory methods must delegate to it. In PHP 8.5, the `__construct` method serves as the primary constructor. Use static factory methods to provide alternative creation paths, ensuring they prepare arguments and call the primary constructor.
- Leverage constructor property promotion and `readonly` properties for field initialization.
- Replace any `getX()` with a behavior-revealing method like `x()` and any `setX()` with an action method like `take()`.
- Avoid objects that act as universal service providers; split them into smaller, entity-specific objects.
- Challenge every "-er" name during code review as a sign of procedural design.

### Keep Constructors Free of Any Code Beyond Assignments
Constructors must contain nothing but assignments and fully initialize the object; never use separate `init()` methods or two-step initialization. Any computation inside a constructor is a side effect that breaks composability. Use prestructors for argument pre-processing. (2015-05-07, 2021-08-04, 2023-08-08)

**Why:** Code in constructors performs work immediately, turning object creation into an imperative utility call rather than a declarative composition. This prevents objects from being extended or decorated, because side effects execute before the object owner requests them, wasting resources and coupling logic to instantiation. Two-step initialization hides problems like mutability, fragile base classes, bloated abstractions, or layering violations. Partially constructed objects lead to resource leaks, temporal coupling, and unpredictable behavior.

**How:**
- Move all logic from constructors to methods, performing computations only when called.
- Use decorators (e.g., caching wrappers) to add behavior like memoization without touching the original class.
- Ensure every constructor only assigns arguments to private readonly properties, nothing else. Leverage constructor property promotion for this.
- In PHP 8.5, declare the class as `readonly class` and use `private readonly Type $prop` in the promoted constructor.
- Delegate any argument transformation to a dedicated prestructor object or private static method that prepares values and passes them to the primary constructor.
- Make all fields `readonly` and pass dependencies through constructors, eliminating mutable state.
- Replace inheritance with composition to avoid fragile base class interactions.
- Limit a class to at most three attributes and avoid dumping configuration objects via a second step.

```php
readonly class NamePrestructor {
    public function __construct(private readonly string $raw) {}
    public function value(): string { return trim($this->raw); }
}

readonly class User {
    public function __construct(private readonly string $name) {}
    public static function fromFullName(string $full): self {
        return new self((new NamePrestructor($full))->value());
    }
}
```

### Avoid the Builder Pattern
If you need a Builder, your objects are too complex—refactor them to be creatable through constructors alone. (2016-02-03)

**Why:** The Builder pattern encourages big, complex objects that are hard to maintain and understand. A well-designed object should be simple enough to initialize directly via its constructor, without a separate building process. Builders are a symptom of poor decomposition.

**How:**
- Break large objects into smaller, cohesive ones that each have clear, simple constructors.
- Replace Builder classes with direct constructor calls, passing only essential dependencies.
- If construction logic is complex, extract it into factory methods on the class itself, not a separate Builder.
- In PHP 8.5, use constructor property promotion and `readonly` classes for direct initialization.
- Reject any design that requires a multi-step build process for a single object.

### Always Instantiate Objects via Constructors, Never Static Factory Methods
Always create objects using the `new` operator and primary constructors; never use static factory methods on classes or interfaces. (2017-11-14, 2017-10-03)

**Why:** Static factory methods hide creation logic, break polymorphism, violate encapsulation, and move decision-making outside the object. Every claimed benefit (named creation, caching, subtyping) can be achieved through proper decomposition into specific classes, stores, and decorators while keeping `new` explicit.

**How:**
- Split ambiguous creation needs into distinct entity classes (e.g., `HexColor` and `RGBColor`) so the class name itself provides semantics.
- Introduce a separate store object (e.g., `Palette`) to handle caching and reuse instead of static maps.
- Replace conditional subtype selection with decorators that wrap the base implementation.
- In PHP 8.5, rely exclusively on constructor property promotion inside `readonly` classes; delegate any alternative paths to the primary constructor.

### Keep `new` Out of Methods
Never use the `new` operator inside methods; move all object instantiation into constructors. (2018-01-02)

**Why:** Using `new` in methods creates unbreakable dependencies, making classes impossible to reuse or test. When a method instantiates a concrete class, it couples the object to that specific implementation forever. Pushing `new` to constructors enables dependency injection, allowing flexible substitution of implementations and easier unit testing.

**How:**
- Replace all `new` calls in methods with constructor-injected dependencies.
- Provide secondary constructors that supply default dependencies for convenience.
- Ensure primary constructors accept abstractions (interfaces), not concrete implementations.
- Keep methods free of any `new` operators—only use pre-instantiated objects.
- In PHP 8.5, declare dependencies as `private readonly Interface $dep` via constructor property promotion.

### Replace Static Utility Classes with Composable Objects
Never create static utility classes or use static methods. Replace them with objects that encapsulate state and behavior, enabling composition, decoration, and lazy execution by returning deferred computations. (2014-05-05, 2015-02-20, 2016-11-29)

**Why:** Static utilities and methods come from procedural programming, prevent object composition, complicate testing, and often force eager execution with higher memory use. True functional style in an OO context means returning objects or callables that compute only when needed.

**How:**
- Define interfaces for the desired behavior (e.g., `IteratorAggregate`).
- Implement behavior in `readonly` classes that wrap other objects.
- Use decorators to add concerns without modifying existing classes.
- Compose pipelines that defer work until results are actually needed.
- Convert static methods to methods on these objects that provide the result on demand rather than computing eagerly.

Example decorator in PHP 8.5:

```php
readonly class Trimmed implements \IteratorAggregate
{
    public function __construct(
        private readonly \IteratorAggregate $inner
    ) {}

    public function getIterator(): \Traversable
    {
        foreach ($this->inner as $item) {
            yield trim((string)$item);
        }
    }
}
```

### Prefer Composable Decorator Objects Over Monolithic Iteration APIs for Collections
Process collections using small, focused decorator classes that implement `IteratorAggregate` or `Traversable` instead of large monolithic iteration APIs or procedural array functions. (2017-10-10)

**Why:** Huge interfaces violate cohesion and make composability difficult; decorators keep each class small, explicit, and easy to extend while preserving laziness and immutability.

**How:**
- Create decorators such as `Filtered`, `Limited`, or `Unique` that each implement `IteratorAggregate` and perform one transformation.
- Compose by nesting: `new Limited(new Filtered($baseIterator))`.
- Implement iteration logic inside the decorator using generators or internal buffers when adapting sources.
- Avoid mutable state (e.g., counters) inside decorators; keep them stateless or readonly.

### Prefer Composable Decorators Over Imperative Utility Methods
When you need to add behavior to an object, create a decorator class instead of adding a method to the original class or introducing a utility method. (2015-02-26)

**Why:** Utility methods lead to imperative, procedural code that is rigid and hard to reuse. Decorators encapsulate behaviors into separate objects, allowing you to compose them declaratively without executing logic until needed. This keeps interfaces minimal, cohesive, and loosely coupled.

**How:**
- When tempted to add a method like `trim()` or `toUpperCase()` to a class, create a decorator class that wraps the original and adds that behavior instead.
- Compose decorators by nesting them: `new AllCapsText(new TrimmedText(new TextInFile(...)))`.
- Ensure each decorator implements the same interface and delegates to the wrapped object.
- Keep core interfaces minimal—only include methods that are absolutely essential.
- In PHP 8.5, declare the decorator as `readonly class` with `private readonly Interface $inner` via constructor promotion.

### Validate Parameters via Decorators
Use decorators to validate method parameters instead of embedding defensive checks inside the core object. (2016-01-26)

**Why:** Embedding validations bloats classes with non-essential logic, reducing maintainability and cohesion. Decorators extract validation into separate, small, and focused classes, making the core object simpler and more reusable. This approach lets you compose validation behaviors flexibly and avoids expensive checks when they aren't needed.

**How:**
- Define an interface for the operation and implement the core logic in one `readonly` class with no validation.
- Create decorator classes that perform specific validations (e.g., null checks, existence checks) before delegating to the wrapped object.
- Compose the core object with one or more validating decorators when defensive behavior is required.
- Avoid validation annotations or inline checks that mix business logic with protective code.

### Switch Between Vertical and Horizontal Decorating Based on Scale
Start with vertical decorating (nesting) for simple objects; switch to horizontal decorating (a list of features passed to one decorator) when the number of decorators grows. (2015-10-01)

**Why:** Vertical decorating is simpler for small objects with few methods, but becomes rigid and hard to configure as decorators multiply. Horizontal decorating centralizes configuration, easing management of many optional behaviors without deep nesting.

**How:**
- For up to three or four decorators, nest them directly: `new Sorted(new Unique(new Base()))`.
- When more, create a `Modified` decorator that accepts an array of feature objects, each implementing a method like `apply(Traversable $data): Traversable`.
- In PHP 8.5, use `readonly class` with constructor property promotion for the decorator and features.
- Refactor from vertical to horizontal as nesting depth affects readability.

### Avoid Dependency Injection Containers
Avoid dependency injection containers; compose objects manually via plain constructors instead. (2014-10-03)

**Why:** DI containers add unnecessary configuration layers, encourage mutable objects through setter or field injection, and duplicate the natural capabilities of direct instantiation. Constructor injection alone provides testability and decoupling without extra machinery.

**How:**
- Instantiate objects directly with `new ClassName(...)`, passing all dependencies explicitly.
- Use PHP 8.5 constructor property promotion together with `readonly` properties for clean wiring: `public function __construct(private readonly DependencyInterface $dep) {}`.
- Perform wiring in application startup code or simple factory methods; avoid any container configuration or annotations.
- In tests, manually supply mock implementations when constructing the object under test.

### Never Return or Accept NULL
Never return or accept `NULL`. Replace every occurrence with a Null Object constant or a specific exception. (2014-05-13)

**Why:** `NULL` forces ad-hoc checks, creates ambiguous semantics, and leads to mutable or incomplete objects. Object-oriented code should deliver a valid object or fail fast.

**How:**
- Define a Null Object constant inside the class (e.g., `public const NOBODY = new self();`).
- Throw a descriptive exception when a valid object cannot be produced.
- Refactor methods that would return `NULL` to return iterators or other objects instead.

### Provide Default Values Instead of Returning Null
Never return null from a method; always require the caller to supply a default value. (2018-05-22)

**Why:** Returning null forces clients to perform checks and risks runtime errors. Supplying a default makes the contract explicit, keeps code linear, and removes ambiguity about missing results.

**How:**
- Add a mandatory parameter for the default value to any method that may not produce a result.
- Return the supplied default when the computation yields no value.
- Refuse to design methods whose return type allows null.

### Avoid Classic OOP Anti-Patterns
Never use NULL references, utility classes, mutable objects, DTOs, ORMs, singletons, implementation inheritance, static methods, or procedural constructs such as controllers, managers, or validators. (2014-09-10, 2016-09-13, 2016-11-29)

**Why:** These patterns promote procedural thinking, hidden dependencies, and anemic models that violate encapsulation and make code unmaintainable and untestable.

**How:**
- Replace NULL with Null Objects or exceptions.
- Convert utility classes and static methods into real objects with behavior.
- Ensure all objects are immutable and encapsulate their own data.
- Let objects manage their lifecycle instead of relying on singletons or external managers.
- Replace `extends` on concrete classes with `implements` on interfaces; use composition instead of inheritance.
- Never use `protected` members.

### Never Use Singletons
Never use singletons; always pass dependencies explicitly through constructors. (2016-06-27)

**Why:** Singletons create hidden global state, tight coupling, and untestable code. They pretend to be objects but are actually procedural global variables. Dependency injection makes dependencies visible, enforces proper object composition, and keeps objects focused—if an object needs too many dependencies, it’s a sign to split it into smaller, more cohesive objects.

**How:**
- Replace every static singleton access with a private readonly field initialized via constructor injection using property promotion.
- Assemble objects at the entry point using direct `new` calls or simple factories.
- When a constructor grows beyond 3–4 arguments, decompose the class into smaller classes, each with fewer dependencies.

### Never Leak Internal Data
Objects must encapsulate their data completely and never leak it to other objects; data transfer objects and getters are violations of object-oriented principles. Never let data escape the object that owns it—no getters, no public properties. (2016-07-06, 2016-11-21, 2019-03-12)

**Why:** Encapsulation is the core of OOP—objects should hide data and expose only behavior. Exposing data increases its visibility scope, which forces readers to trace logic across the entire codebase instead of understanding isolated, self-contained objects. This directly hurts maintainability because you must comprehend far more context to make changes safely. DTOs turn objects into passive data containers, leading to procedural code where external procedures manipulate naked data instead of asking objects to perform work.

**How:**
- Replace getters with methods that perform actions using the object's own data, like `book.save(database)` instead of exposing data.
- Never return raw data from an object; instead, pass dependencies (e.g., a `PDO` connection) into the object and let it handle the interaction.
- Use constructors to inject data sources so the object manages data internally.
- When you need to share information, pass encapsulated objects or use callbacks, not raw values.
- In PHP 8.5, declare all properties `private readonly` and expose only behavioral methods. Never use public properties.
- Replace all getters with methods that perform the work directly on the object’s own data.
- Design objects so callers ask them to do something, never to give something back.
- Refactor any logic that reads an object’s data from the outside into methods on that object.

### Refine Immutable Resources Instead of Using DTOs
Design logic around immutable resources that refine their state by returning new instances and render themselves through an `Output` interface; never parse data into DTOs or anemic objects. (2019-03-26)

**Why:** DTOs create anemic objects by separating data from behavior, leading to procedural code. Immutable resources encapsulate state and behavior together, support composable refinement, and keep the object in control of its representation.

**How:**
- Define a `Resource` interface with `refine(string $name, mixed $value): self` returning a new immutable instance and `print(Output $output): void` for rendering.
- Process input data by starting with a base resource and iteratively calling `refine()` without intermediate DTO storage.
- Implement resources as `readonly` classes using constructor property promotion to hold refined state.
- Define an `Output` interface for collecting response data (e.g., headers, body) so resources control formatting.
- Compose resources with decorators for additional behaviors such as caching or validation.

### Use Veil Objects to Cache Data Without DTOs
Wrap objects with Veil decorators after bulk data fetches to cache results of specific methods. The veil returns cached values for pre-configured methods and delegates all other calls to the original object. (2020-05-19)

**Why:** Bulk queries are efficient, but exposing raw data as DTOs breaks encapsulation. Veil objects allow performance optimization while keeping objects as behavioral entities that can be further decorated or used polymorphically.

**How:**
- After fetching data in bulk, instantiate a Veil class for each entity, providing the original object and a map of method names to their cached return values.
- Implement Veil as a `readonly class` that implements the same interface as the entity.
- Use constructor property promotion: `public function __construct(private readonly EntityInterface $inner, private readonly array $cachedMethods) {}`.
- In each method implementation, if the method name is in the cache map, return the cached value; otherwise, delegate to the corresponding method on `$inner`.
- For permanent caching in read-only contexts, create an `UnpiercableVeil` variant that always serves from cache without piercing.

### Treat Objects as Representatives of Data
An object is a representative of data, never a container for it. (2016-07-14)

**Why:** Viewing objects as data boxes promotes procedural thinking, exposing internal state and inviting direct data manipulation. Instead, when objects are seen as representatives, you focus on communication and behavior, which enforces true encapsulation and aligns with OOP’s intent.

**How:**
- Never expose object internals via getters or setters; provide methods that perform operations instead.
- Design interfaces that describe what an object does, not what data it holds.
- Treat object attributes as external entities the object represents, not as memory slots it owns.
- Refactor any code that accesses an object’s fields directly to use behavioral methods.

### Respect the Law of Demeter
Never access another object's attributes directly, but freely chain method calls on objects that methods create or return. (2016-07-18)

**Why:** The original Law of Demeter only prohibits sending messages to objects obtained by direct attribute access (which includes getters). Objects built by methods are considered valid arguments, so chaining on them is legal. The misinterpretation as a "one dot" rule comes from equating all non-trivial methods with getters.

**How:**
- Eliminate getters; if a method returns an object, it must not be an internal field.
- Design methods to construct and return new objects, enabling safe chaining.
- Do not refactor valid method chains (like `$book->pages()->last()->text()`) into monolithic methods.
- Audit code for direct field access and replace it with method calls that build objects.

### Limit Coupling Distance to Preserve Encapsulation
Count and minimize the number of subsequent operations performed on any value returned from an object; refactor when the chain of calls on returned data exceeds one step. (2020-10-27)

**Why:** Encapsulation is not absolute—clients can always deconstruct returned data. The distance of coupling measures how far the data travels before use, indicating poor design where objects do not complete their work internally.

**How:**
- Design methods to return self-contained results that require no further processing or parsing by the caller.
- Audit code for chains like `$obj->getData()->transform()->use()` and move the transformation logic into the object or a decorator.
- Introduce a static analysis rule in PHPStan or Psalm to flag long coupling distances.
- Prefer returning new objects or primitives that are final results.

### Encapsulate Database Interactions in Entity Objects
Avoid ORMs by designing objects that encapsulate SQL interactions for real-life entities such as tables and rows. (2014-12-01)

**Why:** ORM violates object-oriented principles by splitting objects into passive data bags and separate engine components, exposing the relational model everywhere. This leads to untestable code, SQL leakage, and complex designs. True objects should hide persistence details and speak SQL internally.

**How:**
- Design interfaces for table (plural) and row (singular) abstractions, e.g., `Posts` and `Post`.
- Implement these interfaces with `readonly` classes that directly execute SQL via `PDO` or a thin wrapper, never exposing sessions or factories.
- Use decorators to optimize queries (e.g., caching) without bloating core classes.
- Wrap transactions in dedicated objects that accept callable logic, keeping them encapsulated.

### Abolish Data Access Objects; Make Entity Objects Responsible for Their Own Persistence
Design entity objects to encapsulate and perform their own database operations; never introduce separate DAO classes that separate data from behavior. (2017-12-05)

**Why:** DAOs tear objects apart into passive data containers and external engines, destroying encapsulation and producing rigid procedural code. True objects are cohesive and manage their full lifecycle, including persistence.

**How:**
- Implement entity interfaces (e.g., `Post`) with methods such as `update()` or `delete()` that execute SQL internally via an injected `PDO` instance.
- Eliminate any DAO layer; when retrieving an object, return a fully capable instance that can interact with the database autonomously.
- Use decorators for cross-cutting persistence concerns such as caching or logging.

### Never Use ActiveRecord
Never use ActiveRecord; design objects that encapsulate SQL instead of inheriting persistence. (2016-07-26)

**Why:** ActiveRecord falsely presents objects as proper behavioral entities while they remain anemic data containers, just shifting the persistence logic into a parent class. This deception continues the procedural paradigm shift, where objects expose data rather than behavior, and conceals the true dependency on the database. The result is a codebase that appears object-oriented but remains fundamentally procedural.

**How:**
- Eliminate all base classes that provide `save()`, `update()`, or similar persistence methods.
- Embed SQL statements directly inside the objects responsible for those data operations using `PDO` or a thin wrapper.
- Refuse to use Data Transfer Objects; make each object fully own its data and persistence logic.
- Avoid any inheritance for persistence; use composition instead.

### Encapsulate External APIs in Immutable Object-Oriented Interfaces
Always encapsulate external API calls inside immutable interfaces that hide procedural details and include dedicated mock implementations. (2014-04-14, 2014-05-14, 2014-05-26)

**Why:** Direct use of procedural SDKs scatters low-level details, prevents safe composition, and complicates unit tests. Immutability guarantees thread safety and side-effect freedom while mocks enable fast, deterministic testing.

**How:**
- Define a narrow interface for each resource exposing only domain methods such as `put(Attributes $attributes): void`.
- Implement the interface in a `readonly` class that delegates to the underlying SDK while keeping all SDK types internal.
- Inject the interface via constructor using PHP 8.5 constructor property promotion and `readonly` properties.
- Provide a separate mock class implementing the same interface for tests.
- Ensure no code outside the wrapper imports or depends on vendor SDK types; mock the interface directly in tests.

### Wrap External Protocols in Simple Interfaces with Decorators
Wrap low-level libraries such as SSH clients behind a minimal interface and enhance behavior with decorators for safety and logging. (2014-09-02)

**Why:** Raw libraries produce verbose boilerplate for error checking and output handling; decorators add cross-cutting concerns orthogonally while keeping core logic clean.

**How:**
- Define a narrow interface with a single method such as `exec(string $command): string`.
- Implement the core class by delegating to the underlying library.
- Create decorator classes such as `SafeShell` that throw on non-zero exit codes and `VerboseShell` that logs output.
- Compose decorators as needed without modifying the original implementation.

### Avoid Type Discrimination with Runtime Checks
Never use `is_a()`, `instanceof`, `get_class()`, or casting to discriminate objects based on their concrete type inside a method. (2015-04-02)

**Why:** Class casting and type checks create hidden coupling to specific types, violating the method's contract and surprising clients when dependencies change. It also bloats methods with type-checking forks, breaking the single responsibility principle.

**How:**
- Replace type checks with polymorphic method calls through interfaces.
- Design objects to fully honor their interfaces so no special-casing is needed inside methods.
- When tempted to check type, refactor the object's type hierarchy or introduce a new interface to eliminate the need.
- Treat all objects entering a method equally based on the declared parameter type, not their runtime class.
- Use PHP 8.5's type system with interfaces and union types to express expectations without runtime discrimination.

### Lazily Buffer Data in Iterator Adapters
When adapting a non-iterable data source to an Iterator, always use an internal buffer and lazily populate it to avoid losing data between calls. (2015-04-30)

**Why:** Calling the source's read method without buffering can skip data because each call may return different bytes. Buffering ensures that a complete batch of data is available for subsequent calls without re-invoking the source. This also keeps the iterator simple and read-only.

**How:**
- Create a class implementing `\Iterator` with a private buffer (e.g., `array` or `SplQueue`).
- Lazily populate the buffer inside the `valid()` method by reading from the source when the buffer is empty.
- In `next()`, advance the position after ensuring validity.
- Override methods to throw `BadMethodCallException` for unsupported operations like removal if the iterator is read-only.
- Document the iterator as not thread-safe; clients must handle synchronization if needed.
- Prefer generators (`yield`) for simple lazy iteration when possible, but use explicit buffering for complex adapters.

Example in PHP 8.5:

```php
readonly class BufferedAdapter implements \Iterator
{
    private array $buffer = [];
    private int $position = 0;

    public function __construct(
        private readonly object $source
    ) {}

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        if ($this->position >= count($this->buffer) && $this->source->hasMore()) {
            $this->buffer[] = $this->source->read();
        }
        return $this->position < count($this->buffer);
    }

    public function current(): mixed
    {
        return $this->buffer[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }
}
```

### Replace Public Static Literals with Encapsulated Classes
Never use public static properties; encapsulate data and behavior together in dedicated classes. (2015-07-06)

**Why:** Public static literals create unbreakable hard-coded dependencies, just like utility classes. They only centralize data, not functionality, which encourages duplication of the same logic everywhere that data is used. True deduplication means wrapping repeated code and its data into a single, reusable object, hiding the constant as a private implementation detail.

**How:**
- When you see the same literal used in multiple places, identify the repeated functionality around it.
- Create a new class that encapsulates that functionality, storing the literal as a private field.
- Replace all direct literal references with instances of the new class.
- If a class can't be extended (like `String`), use composition and a meaningful name (e.g., `new UTF8String(array).toString()`).

Example in PHP 8.5:

```php
readonly class Greeting
{
    private const MESSAGE = 'Hello';

    public function __construct(
        private readonly string $name
    ) {}

    public function text(): string
    {
        return self::MESSAGE . ', ' . $this->name;
    }
}
```

### Replace Getters with Printers
Objects must print their state to media objects instead of exposing it through getters. (2016-04-05)

**Why:** Getters reduce objects to passive data structures, breaking encapsulation and encouraging procedural code.

**How:**
- Implement a `print(Media $media): void` method that delegates data to the media object using a `with(string $name, mixed $value): self` interface.
- Create immutable `readonly` media classes (e.g., `JsonMedia`, `XmlMedia`) that collect data and produce output.
- Avoid any public getter methods; let the object control its representation.
- In PHP 8.5, declare media classes as `readonly` with constructor promotion.

Example:

```php
interface Media
{
    public function with(string $name, mixed $value): self;
    public function output(): string;
}

readonly class JsonMedia implements Media
{
    // implementation accumulating data
}

readonly class User
{
    public function __construct(private readonly string $name) {}

    public function print(Media $media): void
    {
        $media->with('name', $this->name);
    }
}
```

### Replace Attributes with Explicit Object Composition
Avoid using PHP attributes for behavior injection; always compose objects explicitly through constructors and decorators. (2016-04-12)

**Why:** Attributes move functionality outside the object, violating encapsulation and hiding the composition.

**How:**
- Do not use attributes (e.g., `#[Inject]`, `#[Validate]`) to add behavior.
- Pass all dependencies via constructor parameters using property promotion.
- Implement cross-cutting concerns like retry or logging with explicit decorator classes.
- In PHP 8.5, while attributes are available, reserve them for metadata only and prefer direct `new` composition.

### Keep Objects Non-Configurable
Object constructors must only accept real-world identifying properties; behavioral configuration belongs in decorators. (2016-04-19)

**Why:** Configurable parameters bloat classes and mix entity identity with behavioral switches, reducing cohesion.

**How:**
- Limit constructor arguments to coordinates like paths or IDs.
- Extract behavioral options into separate decorator classes that wrap the core object.
- Each variation (e.g., caching, validation) becomes its own `readonly` class implementing the interface.
- Compose at construction time: `new Cached(new Validated(new Core(...)))`

### Design Interfaces with a Single Core Method
Interfaces should declare exactly one essential method; move all convenience and supplementary behavior into decorator classes. (2016-04-26)

**Why:** Overloaded interfaces force implementors to handle unrelated concerns and reduce composability.

**How:**
- Define interfaces with only the primary method (e.g., `read(): string` for a reader).
- Provide convenience methods in a `Smart` decorator that wraps the minimal interface.
- Avoid method overloading in interfaces.
- Refactor any interface with more than one or two methods by extracting decorators.

### Replace If-Then-Else with Decorators
Every if-then-else that can be moved into a decorator must be moved. (2016-08-10)

**Why:** Forking logic that guards or alters behavior doesn’t belong to the core object—it dilutes cohesion and mixes primary functionality with defensive checks. A decorator isolates that conditional, keeping the original class smaller, focused, and more reusable.

**How:**
- Identify any if-then-else that wraps or skips the main logic based on input state.
- Extract that conditional into a new `readonly` decorator class that delegates to the original object only when the condition passes.
- Use the decorator to compose behavior instead of hardcoding the branch inside the method.
- Leave the original class free of any guard clauses that can be externalized.

### Prefer Vertical Decomposition via Decorators
Break down complex objects by wrapping extracted responsibilities in decorators, not by exposing them as separate dependencies to the client. (2016-08-30)

**Why:** Horizontal decomposition forces clients to juggle multiple objects, increasing dependencies and cognitive load. Vertical decomposition encases the composition, preserving a single entry point and reducing overall complexity — the client only needs to know the outermost object. This keeps the system cohesive and maintainable.

**How:**
- Move extra behavior from a god object into a decorator that accepts the original object in its constructor.
- Have the decorator delegate to the original object while adding the new functionality inline.
- Always present a single object to clients, stacking decorators if needed, never requiring them to assemble parts.
- Avoid designs where two or more objects must be called in sequence to achieve a single logical operation.

### Avoid Implementation Inheritance
Never use class-based implementation inheritance for code reuse; use interface subtyping for polymorphism and composition for reuse. (2016-09-13)

**Why:** Implementation inheritance copies code and data from a "dead" parent, treating objects as passive containers—a procedural technique that violates object thinking. It introduces coupling and fragility, while true OOP relies on living, encapsulated objects that collaborate via composition. Subtyping through interfaces preserves polymorphism without exposing internals.

**How:**
- Replace `extends` on concrete classes with `implements` on interfaces to define type hierarchies.
- Use composition and delegation to share behavior instead of inheriting methods and
