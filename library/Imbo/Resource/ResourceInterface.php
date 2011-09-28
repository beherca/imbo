<?php
/**
 * Imbo
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package Imbo
 * @subpackage Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imbo
 */

namespace Imbo\Resource;

use Imbo\Http\Request\RequestInterface;
use Imbo\Http\Response\ResponseInterface;
use Imbo\Database\DatabaseInterface;
use Imbo\Storage\StorageInterface;

/**
 * Resource interface
 *
 * Available resources must implement this interface. They can also extend the abstract resource
 * class (Imbo\Resource\Resource) for convenience.
 *
 * @package Imbo
 * @subpackage Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imbo
 */
interface ResourceInterface {
    /**#@+
     * State constants
     *
     * @var string
     */
    const STATE_PRE  = 'pre';
    const STATE_POST = 'post';
    /**#@-*/

    /**
     * Return an array with the allowed (implemented) HTTP methods for the current resource
     *
     * @return string[]
     */
    function getAllowedMethods();

    /**
     * POST handler
     *
     * @param Imbo\Http\Request\RequestInterface   $request  A request instance
     * @param Imbo\Http\Response\ResponseInterface $response A response instance
     * @param Imbo\Database\DatabaseInterface $database A database instance
     * @param Imbo\Storage\StorageInterface   $storage  A storage instance
     * @throws Imbo\Resource\Exception
     */
    function post(RequestInterface $request, ResponseInterface $response, DatabaseInterface $database, StorageInterface $storage);

    /**
     * GET handler
     *
     * @param Imbo\Http\Request\RequestInterface   $request  A request instance
     * @param Imbo\Http\Response\ResponseInterface $response A response instance
     * @param Imbo\Database\DatabaseInterface $database A database instance
     * @param Imbo\Storage\StorageInterface   $storage  A storage instance
     * @throws Imbo\Resource\Exception
     */
    function get(RequestInterface $request, ResponseInterface $response, DatabaseInterface $database, StorageInterface $storage);

    /**
     * HEAD handler
     *
     * @param Imbo\Http\Request\RequestInterface   $request  A request instance
     * @param Imbo\Http\Response\ResponseInterface $response A response instance
     * @param Imbo\Database\DatabaseInterface $database A database instance
     * @param Imbo\Storage\StorageInterface   $storage  A storage instance
     * @throws Imbo\Resource\Exception
     */
    function head(RequestInterface $request, ResponseInterface $response, DatabaseInterface $database, StorageInterface $storage);

    /**
     * DELETE handler
     *
     * @param Imbo\Http\Request\RequestInterface   $request  A request instance
     * @param Imbo\Http\Response\ResponseInterface $response A response instance
     * @param Imbo\Database\DatabaseInterface $database A database instance
     * @param Imbo\Storage\StorageInterface   $storage  A storage instance
     * @throws Imbo\Resource\Exception
     */
    function delete(RequestInterface $request, ResponseInterface $response, DatabaseInterface $database, StorageInterface $storage);

    /**
     * OPTIONS handler
     *
     * @param Imbo\Http\Request\RequestInterface   $request  A request instance
     * @param Imbo\Http\Response\ResponseInterface $response A response instance
     * @param Imbo\Database\DatabaseInterface $database A database instance
     * @param Imbo\Storage\StorageInterface   $storage  A storage instance
     * @throws Imbo\Resource\Exception
     */
    function options(RequestInterface $request, ResponseInterface $response, DatabaseInterface $database, StorageInterface $storage);

    /**
     * PUT handler
     *
     * @param Imbo\Http\Request\RequestInterface   $request  A request instance
     * @param Imbo\Http\Response\ResponseInterface $response A response instance
     * @param Imbo\Database\DatabaseInterface $database A database instance
     * @param Imbo\Storage\StorageInterface   $storage  A storage instance
     * @throws Imbo\Resource\Exception
     */
    function put(RequestInterface $request, ResponseInterface $response, DatabaseInterface $database, StorageInterface $storage);
}